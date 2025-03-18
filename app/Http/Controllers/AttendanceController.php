<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Team;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Leave;
use App\Models\Holiday;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use Carbon\CarbonInterval;
use App\Models\Designation;
use App\Traits\ImportExcel;
use App\Traits\EmployeeDashboard;
use Illuminate\Http\Request;
use App\Models\CompanyAddress;
use App\Exports\AttendanceExport;
use App\Imports\AttendanceImport;
use App\Jobs\ImportAttendanceJob;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\EmployeeShiftSchedule;
use App\Exports\AttendanceByMemberExport;
use App\Http\Requests\ClockIn\ClockInRequest;
use App\Http\Requests\Attendance\StoreAttendance;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Attendance\StoreBulkAttendance;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Models\Company;
use App\Models\LogTimeFor;
use App\Models\EmployeeShift;
use App\Scopes\ActiveScope;

class AttendanceController extends AccountBaseController
{

    use ImportExcel, EmployeeDashboard {
        EmployeeDashboard::attendanceShift as protected attendanceShiftFromTrait;
    }

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.attendance';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('attendance', $this->user->modules));
            $this->viewAttendancePermission = user()->permission('view_attendance');

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $attendance = Attendance::find($request->employee_id);

        if ($request->employee_id) {
            abort_403(!(
                $this->viewAttendancePermission == 'all'
                || ($this->viewAttendancePermission == 'added' && $attendance->added_by == user()->id)
                || ($this->viewAttendancePermission == 'owned' && $attendance->user_id == user()->id)
                || ($this->viewAttendancePermission == 'both' && ($attendance->added_by == user()->id || $attendance->user_id == user()->id))));
        }
        else {
            abort_403(!in_array($this->viewAttendancePermission, ['all', 'added', 'owned', 'both']));
        }

        if (request()->ajax()) {
            return $this->summaryData($request);
        }

        if ($this->viewAttendancePermission == 'owned') {
            $this->employees = User::where('id', user()->id)->get();

        }
        else {
            $this->employees = User::allEmployees(null, false, ($this->viewAttendancePermission == 'all' ? 'all' : null));
        }

        $now = now();
        $this->year = $now->format('Y');
        $this->month = $now->format('m');
        $this->departments = Team::all();
        $this->designations = Designation::all();

        return view('attendances.index', $this->data);
    }

    public function summaryData($request)
    {
        $viewEmployeePermission = user()->permission('view_employees');
        $employees = User::with(
            [
                'employeeDetail.designation:id,name',
                'attendance' => function ($query) use ($request) {
                    $startOfMonth = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
                    $officestartTimeDB = Carbon::createFromFormat('Y-m-d H:i:s', $startOfMonth, company()->timezone);
                    $startOfMonth = $startOfMonth->subMinutes($officestartTimeDB->offset / 60);

                    $endOfMonth = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth();
                    $officeEndTimeDB = Carbon::createFromFormat('Y-m-d H:i:s', $endOfMonth, company()->timezone);
                    $endOfMonth = $endOfMonth->subMinutes($officeEndTimeDB->offset / 60);

                    $query->whereBetween('attendances.clock_in_time', [$startOfMonth, $endOfMonth]);

                    // $query->orwhereRaw('MONTH(attendances.clock_in_time) = ?', [$request->month])
                        // ->orwhereRaw('YEAR(attendances.clock_in_time) = ?', [$request->year]);

                    if ($this->viewAttendancePermission == 'added') {
                        $query->where('attendances.added_by', user()->id);

                    }
                    elseif ($this->viewAttendancePermission == 'owned') {
                        $query->where('attendances.user_id', user()->id);
                    }
                },
                'leaves' => function ($query) use ($request) {
                    $query->whereRaw('MONTH(leaves.leave_date) = ?', [$request->month])
                        ->whereRaw('YEAR(leaves.leave_date) = ?', [$request->year])
                        ->where('status', 'approved');
                },
                'shifts' => function ($query) use ($request) {
                    $query->whereRaw('MONTH(employee_shift_schedules.date) = ?', [$request->month])
                        ->whereRaw('YEAR(employee_shift_schedules.date) = ?', [$request->year]);
                },
                'leaves.type', 'shifts.shift', 'attendance.shift']
        )->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.id', 'users.name','users.inactive_date', 'users.email', 'users.created_at', 'employee_details.department_id', 'employee_details.user_id', 'employee_details.added_by', 'users.image')
            ->onlyEmployee()
            ->withoutGlobalScope(ActiveScope::class)
            ->where(function ($query) use ($request) {
                // $query->whereNull('users.inactive_date')
                $query->where('users.status','active')
                      ->orWhere(function ($subQuery) use ($request) {
                          $subQuery->whereRaw('YEAR(users.inactive_date) >= ?', [$request->year])
                                ->whereRaw('MONTH(users.inactive_date) >= ?', [$request->month]);
                      });
            })
            ->groupBy('users.id');

        if ($request->department != 'all') {
            $employees = $employees->where('employee_details.department_id', $request->department);
        }

        if ($request->designation != 'all') {
            $employees = $employees->where('employee_details.designation_id', $request->designation);
        }

        if ($request->userId != 'all') {
            $employees = $employees->where('users.id', $request->userId);
        }

        if ($viewEmployeePermission == 'owned') {
            $employees = $employees->where('users.id', user()->id);
        }
        elseif ($viewEmployeePermission == 'both') {
            $employees = $employees->where('employee_details.user_id', user()->id)
                                ->orWhere('employee_details.added_by', user()->id);
        }
        elseif ($viewEmployeePermission == 'added') {
            $employees = $employees->where('employee_details.added_by', user()->id);
        }

        $employees = $employees->get();
        $user = user();
        $this->holidays = Holiday::whereRaw('MONTH(holidays.date) = ?', [$request->month])->whereRaw('YEAR(holidays.date) = ?', [$request->year])->get();

        $final = [];
        $holidayOccasions = [];
        $leaveReasons = [];

        $this->daysInMonth = Carbon::parse('01-' . $request->month . '-' . $request->year)->daysInMonth;
        $now = now()->timezone($this->company->timezone);
        $requestedDate = Carbon::parse(Carbon::parse('01-' . $request->month . '-' . $request->year))->endOfMonth();

        foreach ($employees as $employee) {

            $dataBeforeJoin = null;

            $dataTillToday = array_fill(1, $now->copy()->format('d'), 'Absent');
            $dataTillRequestedDate = array_fill(1, (int)$this->daysInMonth, 'Absent');
            $daysTofill = ((int)$this->daysInMonth - (int)$now->copy()->format('d'));

            if (($now->copy()->format('d') != $this->daysInMonth) && !$requestedDate->isPast()) {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), (($daysTofill >= 0 ? $daysTofill : 0)), '-');
            }
            else {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), (($daysTofill >= 0 ? $daysTofill : 0)), 'Absent');
            }

            if (!$requestedDate->isPast()) {
                $final[$employee->id . '#' . $employee->name] = array_replace($dataTillToday, $dataFromTomorrow);

            }
            else {
                $final[$employee->id . '#' . $employee->name] = array_replace($dataTillRequestedDate, $dataFromTomorrow);
            }

            $shiftScheduleCollection = $employee->shifts->keyBy('date');


            foreach ($employee->shifts as $shifts) {
                if ($shifts->shift->shift_name == 'Day Off') {
                    $final[$employee->id . '#' . $employee->name][$shifts->date->day] = 'Day Off';
                }

            }

            $firstAttendanceProcessed = [];

            foreach ($employee->attendance as $attendance) {
                $clockInTimeUTC = $attendance->clock_in_time->timezone(company()->timezone)->toDateTimeString();
                $clockInTime = Carbon::createFromFormat('Y-m-d H:i:s', $clockInTimeUTC, 'UTC');
                $startOfDayKey = $clockInTime->startOfDay()->toDateTimeString();

                $shiftSchedule = $shiftScheduleCollection[$startOfDayKey] ?? null;

                if ($shiftSchedule) {
                    $shift = $shiftSchedule->shift;
                    $shiftStartTime = Carbon::parse($clockInTime->toDateString() . ' ' . $shift->office_start_time);
                    $shiftEndTime = Carbon::parse($clockInTime->toDateString() . ' ' . $shift->office_end_time);

                    // Determine if the attendance is within the shift time, the previous day's shift, or otherwise
                    $isWithinShift = $clockInTime->between($shiftStartTime, $shiftEndTime);
                    $isPreviousShift = $clockInTime->betweenIncluded($shiftStartTime->subDay(), $shiftEndTime->subDay());
                    $isAssignedShift = $attendance->employee_shift_id == $shift->id;

                }
                else {
                    $isWithinShift = $isPreviousShift = $isAssignedShift = false;
                }

                if (!isset($isHalfDay[$employee->id][$startOfDayKey]) && !isset($isLate[$employee->id][$startOfDayKey])) {
                    $isHalfDay[$employee->id][$startOfDayKey] = $isLate[$employee->id][$startOfDayKey] = false;
                }

                // Check if this is the first attendance of the day for this employee
                if (!isset($firstAttendanceProcessed[$employee->id][$startOfDayKey])) {
                    $firstAttendanceProcessed[$employee->id][$startOfDayKey] = true; // Mark as processed

                    // Apply "half day" or "late" logic only if it's the first attendance
                    $isHalfDay[$employee->id][$startOfDayKey] = $attendance->half_day == 'yes';
                    $isLate[$employee->id][$startOfDayKey] = $attendance->late == 'yes';

                }

                $iconClassKey = $isHalfDay[$employee->id][$startOfDayKey] ? 'star-half-alt text-red' : ($isLate[$employee->id][$startOfDayKey] ? 'exclamation-circle text-warning' : 'check text-success');

                // Tooltip title based on attendance status or presence
                $tooltipTitle = $attendance->employee_shift_id ? $attendance->shift->shift_name : __('app.present');

                // Construct the attendance HTML once
                $attendanceHtml = "<a href=\"javascript:;\" data-toggle=\"tooltip\" data-original-title=\"{$tooltipTitle}\" class=\"view-attendance\" data-attendance-id=\"{$attendance->id}\"><i class=\"fa fa-{$iconClassKey}\"></i></a>";

                // Determine the day to assign the attendanceHtml
                if ($isWithinShift || $isAssignedShift || $isPreviousShift) {
                    $dayToAssign = $isPreviousShift ? $clockInTime->copy()->subDay()->day : $clockInTime->day;
                    $final[$employee->id . '#' . $employee->name][$dayToAssign] = $attendanceHtml;

                }
                else {
                    $final[$employee->id . '#' . $employee->name][$clockInTime->day] = $attendanceHtml;
                }
            }

            $emplolyeeName = view('components.employee', [
                'user' => $employee
            ]);

            $final[$employee->id . '#' . $employee->name][] = $emplolyeeName;

            if ($employee->employeeDetail->joining_date->greaterThan(Carbon::parse(Carbon::parse('01-' . $request->month . '-' . $request->year)))) {
                if ($request->month == $employee->employeeDetail->joining_date->format('m') && $request->year == $employee->employeeDetail->joining_date->format('Y')) {
                    if ($employee->employeeDetail->joining_date->format('d') == '01') {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->format('d'), '-');
                    }
                    else {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->subDay()->format('d'), '-');
                    }
                }

                if (($request->month < $employee->employeeDetail->joining_date->format('m') && $request->year == $employee->employeeDetail->joining_date->format('Y')) || $request->year < $employee->employeeDetail->joining_date->format('Y')) {
                    $dataBeforeJoin = array_fill(1, $this->daysInMonth, '-');
                }
            }

            if (Carbon::parse('01-' . $request->month . '-' . $request->year)->isFuture()) {
                $dataBeforeJoin = array_fill(1, $this->daysInMonth, '-');
            }

            if (!is_null($dataBeforeJoin)) {
                $final[$employee->id . '#' . $employee->name] = array_replace($final[$employee->id . '#' . $employee->name], $dataBeforeJoin);
            }

            foreach ($employee->leaves as $leave) {
                if ($leave->duration == 'half day') {
                    if ($final[$employee->id . '#' . $employee->name][$leave->leave_date->day] == '-' || $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] == 'Absent') {
                        $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] = 'Half Day';
                    }
                }
                else {
                    $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] = 'Leave';
                    $leaveReasons[$employee->id][$leave->leave_date->day] = $leave->type->type_name . ': ' . $leave->reason;
                }

            }

            foreach ($this->holidays as $holiday) {
                $departmentId = $employee->employeeDetail->department_id;
                $designationId = $employee->employeeDetail->designation_id;
                $employmentType = $employee->employeeDetail->employment_type;


                $holidayDepartment = (!is_null($holiday->department_id_json)) ? json_decode($holiday->department_id_json) : [];
                $holidayDesignation = (!is_null($holiday->designation_id_json)) ? json_decode($holiday->designation_id_json) : [];
                $holidayEmploymentType = (!is_null($holiday->employment_type_json)) ? json_decode($holiday->employment_type_json) : [];

                if (((in_array($departmentId, $holidayDepartment) || $holiday->department_id_json == null) &&
                    (in_array($designationId, $holidayDesignation) || $holiday->designation_id_json == null) &&
                    (in_array($employmentType, $holidayEmploymentType) || $holiday->employment_type_json == null))
                ) {


                    if ($final[$employee->id . '#' . $employee->name][$holiday->date->day] == 'Absent' || $final[$employee->id . '#' . $employee->name][$holiday->date->day] == '-') {
                        $final[$employee->id . '#' . $employee->name][$holiday->date->day] = 'Holiday';
                        $holidayOccasions[$holiday->date->day] = $holiday->occassion;
                    }
                }
            }
        }

        $this->employeeAttendence = $final;
        $this->holidayOccasions = $holidayOccasions;
        $this->leaveReasons = $leaveReasons;

        $this->weekMap = Holiday::weekMap('D');

        $this->month = $request->month;
        $this->year = $request->year;

        $view = view('attendances.ajax.summary_data', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_attendance');
        $attendance = Attendance::with('user', 'user.employeeDetail', 'location')->findOrFail($id);

        $attendanceSettings = EmployeeShiftSchedule::with('shift')->where('user_id', $attendance->user_id)
            ->whereDate('date', Carbon::parse($attendance->clock_in_time)->toDateString())
            ->first();

        if ($attendanceSettings) {
            $this->attendanceSettings = $attendanceSettings->shift;

        } else {
            $this->attendanceSettings = AttendanceSetting::first()->shift; // Do not get this from session here
        }

        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && $attendance->added_by == user()->id)
            || ($viewPermission == 'owned' && $attendance->user->id == user()->id)
            || ($viewPermission == 'both' && ($attendance->added_by == user()->id || $attendance->user->id == user()->id))
        )
        );

        $this->attendanceActivity = Attendance::userAttendanceByDate($attendance->clock_in_time, $attendance->clock_in_time, $attendance->user_id);

        $this->attendanceActivity->load('shift');

        $attendanceActivity = clone $this->attendanceActivity;
        $attendanceActivity = $attendanceActivity->reverse()->values();

        $settingStartTime = Carbon::createFromFormat('H:i:s', $this->attendanceSettings->office_start_time, $this->company->timezone);
        $defaultEndTime = $settingEndTime = Carbon::createFromFormat('H:i:s', $this->attendanceSettings->office_end_time, $this->company->timezone);

        if ($settingStartTime->gt($settingEndTime)) {
            $settingEndTime->addDay();
        }

        if ($settingEndTime->greaterThan(now()->timezone($this->company->timezone))) {
            $defaultEndTime = now()->timezone($this->company->timezone);
        }

        $this->totalTime = 0;

        foreach ($attendanceActivity as $key => $activity) {
            if ($key == 0) {
                $this->firstClockIn = $activity;
                // $this->attendanceDate = ($activity->shift_start_time) ? Carbon::parse($activity->shift_start_time) : Carbon::parse($this->firstClockIn->clock_in_time)->timezone($this->company->timezone);
                $this->attendanceDate = ($activity->clock_in_time) ? Carbon::parse($activity->clock_in_time)->timezone($this->company->timezone) : Carbon::parse($this->firstClockIn->clock_in_time)->timezone($this->company->timezone);
                $this->startTime = Carbon::parse($this->firstClockIn->clock_in_time)->timezone($this->company->timezone);
            }

            $this->lastClockOut = $activity;

            if (!is_null($this->lastClockOut->clock_out_time)) {
                $this->endTime = Carbon::parse($this->lastClockOut->clock_out_time)->timezone($this->company->timezone);

            }
            elseif (($this->lastClockOut->clock_in_time->timezone($this->company->timezone)->format('Y-m-d') != now()->timezone($this->company->timezone)->format('Y-m-d')) && is_null($this->lastClockOut->clock_out_time)) { // When date changed like night shift
                $this->endTime = Carbon::parse($this->startTime->format('Y-m-d') . ' ' . $this->attendanceSettings->office_end_time, $this->company->timezone);

                if ($this->startTime->gt($this->endTime)) {
                    $this->endTime->addDay();
                }

                if ($this->endTime->gt(now()->timezone($this->company->timezone))) {
                    $this->endTime = now()->timezone($this->company->timezone);
                }

                $this->notClockedOut = true;

            }
            else {
                $this->endTime = $defaultEndTime;

                if ($this->startTime->gt($this->endTime)) {
                    $this->endTime = now()->timezone($this->company->timezone);
                }

                $this->notClockedOut = true;
            }

            $this->totalTime = $this->totalTime + $this->endTime->timezone($this->company->timezone)->diffInSeconds($activity->clock_in_time->timezone($this->company->timezone));
        }

        $this->maxClockIn = $attendanceActivity->count() < $this->attendanceSettings->clockin_in_day;
        /** @phpstan-ignore-next-line */
        $this->totalTime = CarbonInterval::formatHuman($this->totalTime, true);

        $this->attendance = $attendance;

        return view('attendances.ajax.show', $this->data);

    }

    public function edit($id)
    {

        $attendance = Attendance::findOrFail($id);

        $attendanceSettings = EmployeeShiftSchedule::with('shift')->where('user_id', $attendance->user_id)->where('date', $attendance->clock_in_time->format('Y-m-d'))->first();


        if ($attendanceSettings) {
            $this->attendanceSettings = $attendanceSettings->shift;

        }
        else {

            $this->attendanceSettings = attendance_setting()->shift; // Do not get this from session here
        }


        $this->date = $attendance->clock_in_time->timezone($this->company->timezone)->format('Y-m-d');
        $this->row = $attendance;
        $this->clock_in = 1;
        $this->userid = $attendance->user_id;
        $this->total_clock_in = Attendance::where('user_id', $attendance->user_id)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $attendance->clock_in_time->format('Y-m-d')) // $his->date
            ->whereNull('attendances.clock_out_time')->count();
        $this->type = 'edit';
        $this->location = CompanyAddress::all();
        $this->attendanceUser = User::findOrFail($attendance->user_id);

        $this->maxAttendanceInDay = $this->attendanceSettings->clockin_in_day;

        return view('attendances.ajax.edit', $this->data);
    }

    public function update(ClockInRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $carbonDate = Carbon::parse($request->attendance_date, $this->company->timezone);

        $date = $carbonDate->format('Y-m-d');
        $clockIn = Carbon::createFromFormat('Y-m-d ' . $this->company->time_format, $date . ' ' . $request->clock_in_time, $this->company->timezone);

        if ($request->clock_out_time != '') {
            $clockOut = Carbon::createFromFormat('Y-m-d ' . $this->company->time_format, $date . ' ' . $request->clock_out_time, $this->company->timezone);

            if ($clockIn->gt($clockOut) && !is_null($clockOut)) {
                $clockOut = $clockOut->addDay();
            }
        }
        else {
            $clockOut = null;
        }

        $attendances = Attendance::where('user_id', $request->user_id)
            ->whereDate('clock_in_time', $date)
            ->get();

        $recordBeingEdited = $attendances->find($id);

        $conflictingAttendance = $attendances->filter(function ($item) use ($clockIn, $clockOut, $recordBeingEdited) {
            if ($item->id === $recordBeingEdited->id) {
                return false;
            }

            $clockInTime = $item->clock_in_time->timezone($this->company->timezone);
            $clockOutTime = $item->clock_out_time->timezone($this->company->timezone);

            return (
                ($clockInTime->lt($clockOut) && $clockOutTime->gt($clockIn))
            );
        })->first();

        if ($conflictingAttendance) {
            return Reply::error(__('messages.attendanceMarked'));
        }

        $attendance->user_id = $request->user_id;
        $attendance->clock_in_time = $clockIn->copy()->timezone(config('app.timezone'));
        $attendance->clock_in_ip = $request->clock_in_ip;
        $attendance->clock_out_time = $clockOut?->copy()->timezone(config('app.timezone'));
        $attendance->auto_clock_out = 0;
        $attendance->clock_out_ip = $request->clock_out_ip;
        $attendance->working_from = $request->working_from;
        $attendance->work_from_type = $request->work_from_type;
        $attendance->location_id = $request->location;
        $attendance->late = ($request->has('late')) ? 'yes' : 'no';
        $attendance->half_day = ($request->has('halfday')) ? 'yes' : 'no';
        $attendance->half_day_type = ($request->has('half_day_duration') && $request->has('halfday')) ? $request->half_day_duration : null;
        $attendance->save();

        return Reply::success(__('messages.attendanceSaveSuccess'));
    }

    public function mark(Request $request, $userid, $day, $month, $year)
    {
        $this->date = Carbon::createFromFormat('d-m-Y', $day . '-' . $month . '-' . $year)->format('Y-m-d');

        $attendanceSettings = EmployeeShiftSchedule::with('shift')->where('user_id', $userid)->where('date', $this->date)->first();

        if ($attendanceSettings) {
            $this->attendanceSettings = $attendanceSettings->shift;

        }
        else {
            $this->attendanceSettings = attendance_setting()->shift; // Do not get this from session here
        }

        $this->row = Attendance::attendanceByUserDate($userid, $this->date);
        $this->clock_in = 0;
        $this->total_clock_in = Attendance::where('user_id', $userid)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $this->date)
            ->whereNull('attendances.clock_out_time')->count();

        $this->userid = $userid;
        $this->attendanceUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($userid);
        $this->type = 'add';
        $this->maxAttendanceInDay = $this->attendanceSettings->clockin_in_day;
        $this->location = CompanyAddress::all();

        return view('attendances.ajax.edit', $this->data);
    }

    public function store(StoreAttendance $request)
    {
        $carbonDate = Carbon::parse($request->attendance_date, $this->company->timezone);
        $date = $carbonDate->format('Y-m-d');
        $clockIn = Carbon::createFromFormat('Y-m-d ' . $this->company->time_format, $date . ' ' . $request->clock_in_time, $this->company->timezone);

        $attendanceSettings = EmployeeShiftSchedule::with('shift')->where('user_id', $request->user_id)->where('date', $clockIn)->first();

        if ($attendanceSettings) {
            $this->attendanceSettings = $attendanceSettings->shift;

        }
        else {
            $this->attendanceSettings = AttendanceSetting::first()->shift; // Do not get this from session here
        }

        if ($request->clock_out_time != '') {
            $clockOut = Carbon::createFromFormat('Y-m-d ' . $this->company->time_format, $date . ' ' . $request->clock_out_time, $this->company->timezone);

            if ($clockIn->gt($clockOut) && !is_null($clockOut)) {
                $clockOut = $clockOut->addDay();
            }
        }
        else {
            $clockOut = null;
        }

        $attendance = Attendance::where('user_id', $request->user_id)
            ->whereBetween('clock_in_time', [$carbonDate->copy()->subDay(), $carbonDate->copy()->addDay()])
            ->whereNull('clock_out_time')
            ->get();

        $attendance = $attendance->filter(function ($item) use ($date) {
            return $item->clock_in_time->timezone($this->company->timezone)->format('Y-m-d') == $date;
        })->first();

        $startTimestamp = $date . ' ' . $this->attendanceSettings->office_start_time;
        $endTimestamp = $date . ' ' . $this->attendanceSettings->office_end_time;
        $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTimestamp, $this->company->timezone);
        $officeEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTimestamp, $this->company->timezone);

        if ($this->attendanceSettings->shift_type == 'strict') {
            $clockInCount = Attendance::getTotalUserClockInWithTime($officeStartTime, $officeEndTime, $request->user_id);

        } else {
            $clockInCount = Attendance::whereDate('clock_in_time', $officeStartTime->copy()->toDateString())
            ->where('user_id', $request->user_id)
            ->count();
        }

        $employeeShiftId = $this->attendanceSettings->id;

        $shiftStartTime = $clockIn->format('Y-m-d') . ' ' . $this->attendanceSettings->office_start_time;

        if (Carbon::parse($this->attendanceSettings->office_start_time)->gt(Carbon::parse($this->attendanceSettings->office_end_time))) {
            $shiftEndTime = $clockIn->addDay()->format('Y-m-d') . ' ' . $this->attendanceSettings->office_end_time;
        }
        else {
            $shiftEndTime = $clockIn->format('Y-m-d') . ' ' . $this->attendanceSettings->office_end_time;
        }

        if ($attendance && $request->user_id) {
            return Reply::error(__('messages.attendanceMarked'));
        }
        else {
            $attendances = Attendance::where('user_id', $request->user_id)
                ->whereBetween('clock_in_time', [$carbonDate->copy()->subDay(), $carbonDate->copy()->addDay()])
                ->get();

            $attendances = $attendances->filter(function ($item) use ($date) {
                return $item->clock_in_time->timezone($this->company->timezone)->format('Y-m-d') == $date;
            });

            foreach ($attendances as $item) {
                if (!(!$item->clock_in_time->timezone($this->company->timezone)->lt($clockIn) && $item->clock_out_time->timezone($this->company->timezone)->gt($clockIn) && !$item->clock_in_time->timezone($this->company->timezone)->lt($clockOut))) {
                    if (!($item->clock_out_time->timezone($this->company->timezone)->lt($clockIn))) {
                        return Reply::error(__('messages.attendanceMarked'));
                    }
                }
            }
        }

        if (!is_null($attendance) && !$request->user_id) {
            $attendance->update([
                'user_id' => $request->user_id,
                'clock_in_time' => $clockIn->copy()->timezone(config('app.timezone')),
                'clock_in_ip' => $request->clock_in_ip,
                'clock_out_time' => $clockOut?->copy()->timezone(config('app.timezone')),
                'clock_out_ip' => $request->clock_out_ip,
                'working_from' => $request->working_from,
                'location_id' => $request->location,
                'work_from_type' => $request->work_from_type,
                'employee_shift_id' => $employeeShiftId,
                'shift_start_time' => $shiftStartTime,
                'shift_end_time' => $shiftEndTime,
                'late' => ($request->has('late')) ? 'yes' : 'no',
                'half_day' => ($request->has('halfday')) ? 'yes' : 'no',
                'half_day_type' => ($request->has('half_day_duration') && $request->has('halfday')) ? $request->half_day_duration : null
            ]);
        }
        else {
            $leave = Leave::where([
                ['user_id', $request->user_id],
                ['leave_date', $request->attendance_date]
            ])
                ->whereIn('duration', ['half day', 'single', 'multiple'])
                ->whereIn('status', ['approved', 'pending'])
                ->first();

            if (isset($leave)) {
                $leave->update(['status' => 'rejected']);
            }

            // Check maximum attendance in a day
            if ($clockInCount < $this->attendanceSettings->clockin_in_day || $request->user_id) {
                Attendance::create([
                    'user_id' => $request->user_id,
                    'clock_in_time' => $clockIn->copy()->timezone(config('app.timezone')),
                    'clock_in_ip' => $request->clock_in_ip,
                    'clock_out_time' => $clockOut?->copy()->timezone(config('app.timezone')),
                    'clock_out_ip' => $request->clock_out_ip,
                    'working_from' => $request->working_from,
                    'location_id' => $request->location,
                    'late' => ($request->has('late')) ? 'yes' : 'no',
                    'employee_shift_id' => $employeeShiftId,
                    'shift_start_time' => $shiftStartTime,
                    'shift_end_time' => $shiftEndTime,
                    'work_from_type' => $request->work_from_type,
                    'half_day' => ($request->has('halfday')) ? 'yes' : 'no',
                    'half_day_type' => ($request->has('half_day_duration') && $request->has('halfday')) ? $request->half_day_duration : null
                ]);
            }
            else {
                return Reply::error(__('messages.maxClockin'));
            }
        }

        return Reply::success(__('messages.attendanceSaveSuccess'));
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function byMember()
    {
        $this->pageTitle = 'modules.attendance.attendanceByMember';

        abort_403(!(in_array($this->viewAttendancePermission, ['all', 'added', 'owned', 'both'])));

        if ($this->viewAttendancePermission == 'owned') {
            $this->employees = User::where('id', user()->id)->get();

        }
        else {
            $this->employees = User::allEmployees(null, false, ($this->viewAttendancePermission == 'all' ? 'all' : null));
        }

        $now = now();
        $this->year = $now->format('Y');
        $this->month = $now->format('m');

        return view('attendances.by_member', $this->data);
    }

    public function employeeData(Request $request, $startDate = null, $endDate = null, $userId = null)
    {
        // todo ::~
        $ant = []; // Array For attendance Data indexed by similar date
        $dateWiseData = []; // Array For Combine Data

        $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $request->month . '-' . $request->year)->startOfMonth()->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        $userId = $request->userId;

        $attendances = Attendance::userAttendanceByDate($startDate, $endDate, $userId); // Getting Attendance Data
        $holidays = Holiday::getHolidayByDates($startDate, $endDate, $userId); // Getting Holiday Data
        $userId = $request->userId;

        $totalWorkingDays = $startDate->daysInMonth;

        $totalWorkingDays = $totalWorkingDays - count($holidays);
        $daysPresent = Attendance::countDaysPresentByUser($startDate, $endDate, $userId);
        $daysLate = Attendance::countDaysLateByUser($startDate, $endDate, $userId);
        $halfDays = Attendance::countHalfDaysByUser($startDate, $endDate, $userId);
        $daysAbsent = (($totalWorkingDays - $daysPresent) < 0) ? '0' : ($totalWorkingDays - $daysPresent);
        $holidayCount = Count($holidays);

        // Getting Leaves Data
        $leavesDates = Leave::where('user_id', $userId)
            ->where('leave_date', '>=', $startDate)
            ->where('leave_date', '<=', $endDate)
            ->where('status', 'approved')
            ->select('leave_date', 'reason', 'duration')
            ->get()->keyBy('date')->toArray();

        $holidayData = $holidays->keyBy('holiday_date');
        $holidayArray = $holidayData->toArray();

        // Set Date as index for same date clock-ins
        foreach ($attendances as $attand) {
            $clockInTime = Carbon::createFromFormat('Y-m-d H:i:s', $attand->clock_in_time->timezone(company()->timezone)->toDateTimeString(), 'UTC');

            if (!is_null($attand->employee_shift_id)) {
                $shiftStartTime = Carbon::parse($clockInTime->copy()->toDateString() . ' ' . $attand->shift->office_start_time);
                $shiftEndTime = Carbon::parse($clockInTime->copy()->toDateString() . ' ' . $attand->shift->office_end_time);

                if ($shiftStartTime->gt($shiftEndTime)) {
                    $shiftEndTime = $shiftEndTime->addDay();
                }

                $shiftSchedule = EmployeeShiftSchedule::with('shift')->where('user_id', $attand->user_id)->where('date', $attand->clock_in_time->format('Y-m-d'))->first();

                if (($shiftSchedule && $attand->employee_shift_id == $shiftSchedule->shift->id) || is_null($shiftSchedule)) {
                    $ant[$clockInTime->copy()->toDateString()][] = $attand; // Set attendance Data indexed by similar date

                }
                elseif ($clockInTime->betweenIncluded($shiftStartTime, $shiftEndTime)) {
                    $ant[$clockInTime->copy()->toDateString()][] = $attand; // Set attendance Data indexed by similar date

                }
                elseif ($clockInTime->betweenIncluded($shiftStartTime->copy()->subDay(), $shiftEndTime->copy()->subDay())) {
                    $ant[$clockInTime->copy()->subDay()->toDateString()][] = $attand; // Set attendance Data indexed by previous date
                }
            }
            else {
                $ant[$attand->clock_in_date][] = $attand; // Set attendance Data indexed by similar date
            }
        }

        // Set All Data in a single Array
        // @codingStandardsIgnoreStart

        for ($date = $endDate; $date->diffInDays($startDate) > 0; $date->subDay()) {
            // @codingStandardsIgnoreEnd

            if ($date->isPast() || $date->isToday()) {

                // Set default array for record
                $dateWiseData[$date->toDateString()] = [
                    'holiday' => false,
                    'attendance' => false,
                    'leave' => false
                ];

                // Set Holiday Data
                if (array_key_exists($date->toDateString(), $holidayArray)) {
                    $dateWiseData[$date->toDateString()]['holiday'] = $holidayData[$date->toDateString()];
                }

                // Set Attendance Data
                if (array_key_exists($date->toDateString(), $ant)) {
                    $dateWiseData[$date->toDateString()]['attendance'] = $ant[$date->toDateString()];
                }

                // Set Leave Data
                if (array_key_exists($date->toDateString(), $leavesDates)) {
                    $dateWiseData[$date->toDateString()]['leave'] = $leavesDates[$date->toDateString()];
                }

            }

        }

        if ($startDate->isPast() || $startDate->isToday()) {
            // Set default array for record
            $dateWiseData[$startDate->toDateString()] = [
                'holiday' => false,
                'attendance' => false,
                'leave' => false
            ];

            // Set Holiday Data
            if (array_key_exists($startDate->toDateString(), $holidayArray)) {
                $dateWiseData[$startDate->toDateString()]['holiday'] = $holidayData[$startDate->toDateString()];
            }

            // Set Attendance Data
            if (array_key_exists($startDate->toDateString(), $ant)) {
                $dateWiseData[$startDate->toDateString()]['attendance'] = $ant[$startDate->toDateString()];
            }

            // Set Leave Data
            if (array_key_exists($startDate->toDateString(), $leavesDates)) {
                $dateWiseData[$startDate->toDateString()]['leave'] = $leavesDates[$startDate->toDateString()];
            }
        }

        // Getting View data
        $view = view('attendances.ajax.user_attendance', ['dateWiseData' => $dateWiseData, 'global' => $this->company])->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view, 'daysPresent' => $daysPresent, 'daysLate' => $daysLate, 'halfDays' => $halfDays, 'totalWorkingDays' => $totalWorkingDays, 'absentDays' => $daysAbsent, 'holidays' => $holidayCount]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $addPermission = user()->permission('add_attendance');

        abort_403(!($addPermission == 'all' || $addPermission == 'added'));
        $this->employees = User::allEmployees(null, true);
        $this->departments = Team::allDepartments();
        $this->pageTitle = __('modules.attendance.markAttendance');
        $this->year = now()->format('Y');
        $this->month = now()->format('m');
        $this->location = CompanyAddress::all();

        $dateFormat = Company::DATE_FORMATS;
        $this->dateformat = (isset($dateFormat[$this->company->date_format])) ? $dateFormat[$this->company->date_format] : 'DD-MM-YYYY';

        if (request()->ajax()) {
            $html = view('attendances.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'attendances.ajax.create';

        return view('attendances.create', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkMark(StoreBulkAttendance $request)
    {
        $employees = $request->user_id;
        $employeeData = User::withoutGlobalScope(ActiveScope::class)
            ->with('employeeDetail')->whereIn('id', $employees)->get();

        $date = Carbon::createFromFormat('d-m-Y', '01-' . $request->month . '-' . $request->year)->format('Y-m-d');
        $clockIn = Carbon::createFromFormat('Y-m-d ' . $this->company->time_format, $date . ' ' . $request->clock_in_time, $this->company->timezone);

        if ($request->clock_out_time != '') {
            $clockOut = Carbon::createFromFormat('Y-m-d ' . $this->company->time_format, $date . ' ' . $request->clock_out_time, $this->company->timezone);

            if ($clockIn->gt($clockOut) && !is_null($clockOut)) {
                $clockOut = $clockOut->addDay();
            }

        }

        $period = [];

        if ($request->mark_attendance_by == 'month') {
            $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $request->month . '-' . $request->year, $this->company->timezone)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }
        else {
            $dates = explode(',', $request->multi_date);
            if($this->company->date_format == 'd F, Y'){
                $data = preg_match_all('/\d{1,2}\s\w+,\s\d{4}/', $request->multi_date, $dates);
                $dates = $dates[0];
            }
            $startDate = Carbon::createFromFormat($this->company->date_format, trim($dates[0]), $this->company->timezone);
            $endDate = Carbon::createFromFormat($this->company->date_format, trim($dates[1]), $this->company->timezone);
        }

        $multiDates = CarbonPeriod::create($startDate, $endDate);
        $holidayError = false;
        $singleDatePeriod = $startDate->equalTo($endDate);

        foreach ($multiDates as $multiDate) {
            array_push($period, $multiDate);
        }

        $insertData = [];
        $currentDate = now($this->company->timezone);
        $showClockIn = AttendanceSetting::first();

        foreach ($employees as $key => $userId) {
            $userData = $employeeData->where('id', $userId)->first();

            if (request()->has('overwrite_attendance')) {
                Attendance::where('user_id', $userId)
                    ->whereBetween('clock_in_time', [$startDate, $endDate])
                    ->delete();
            }

            // Retrieve holidays based on employee details
            $holidaysForUser = Holiday::where(function ($query) use ($userData) {
                $query->where(function ($subquery) use ($userData) {
                    $subquery->where(function ($q) use ($userData) {
                        $q->where('department_id_json', 'like', '%"' . $userData->employeeDetail->department_id . '"%')
                            ->orWhereNull('department_id_json');
                    });
                    $subquery->where(function ($q) use ($userData) {
                        $q->where('designation_id_json', 'like', '%"' . $userData->employeeDetail->designation_id . '"%')
                            ->orWhereNull('designation_id_json');
                    });
                    $subquery->where(function ($q) use ($userData) {
                        $q->where('employment_type_json', 'like', '%"' . $userData->employeeDetail->employment_type . '"%')
                            ->orWhereNull('employment_type_json');
                    });
                });
            })->get()->pluck('date')->map(function ($date) {
                return $date->format('Y-m-d');
            })->toArray();

            foreach ($period as $date) {

                $leave = Leave::where([
                    ['user_id', $userId],
                    ['leave_date', $date]
                ])
                    ->whereIn('duration', ['half day', 'single', 'multiple'])
                    ->whereIn('status', ['approved', 'pending'])
                    ->first();

                if (isset($leave)) {

                    $leave->update(['status' => 'rejected']);

                    // if ($date->format('Y-m-d') == $leave->leave_date->format('Y-m-d') && is_null($leave->half_day_type)) {
                    //     continue;
                    // }
                }

                $formattedDate = $date->format('Y-m-d');
                $holiday = in_array($formattedDate, $holidaysForUser);
                if ($holiday) {
                    if ($singleDatePeriod) {
                        $holidayError = true;
                        break;
                    }
                    continue;
                }



                $this->attendanceSettings = $this->attendanceShift($showClockIn, $userId, $date, $request->clock_in_time);

                $attendance = Attendance::where('user_id', $userId)
                    ->where(DB::raw('DATE(`clock_in_time`)'), $date->format('Y-m-d'))
                    ->first();

                if (is_null($attendance)
                    && $date->greaterThanOrEqualTo($userData->employeeDetail->joining_date)
                    && $date->lessThanOrEqualTo($currentDate)
                    && !$holiday
                    && $this->attendanceSettings->shift_name != 'Day Off'
                ) { // Attendance should not exist for the user for the same date

                    $clockIn = Carbon::createFromFormat('Y-m-d ' . $this->company->time_format, $date->format('Y-m-d') . ' ' . $request->clock_in_time, $this->company->timezone);

                    $clockOut = Carbon::createFromFormat('Y-m-d ' . $this->company->time_format, $date->format('Y-m-d') . ' ' . $request->clock_out_time, $this->company->timezone);

                    if ($clockIn->gt($clockOut) && !is_null($clockOut)) {

                        $clockOut = $clockOut->addDay();
                    }

                    $insertData[] = [
                        'user_id' => $userId,
                        'company_id' => company()->id,
                        'clock_in_time' => $clockIn->copy()->timezone(config('app.timezone')),
                        'clock_in_ip' => request()->ip(),
                        'clock_out_time' => $clockOut?->copy()->timezone(config('app.timezone')),
                        'clock_out_ip' => request()->ip(),
                        'working_from' => $request->working_from,
                        'work_from_type' => $request->work_from_type,
                        'location_id' => $request->location,
                        'late' => $request->late,
                        'half_day' => $request->half_day,
                        'half_day_type' => ($request->has('half_day_duration') && $request->has('half_day')) ? $request->half_day_duration : null,
                        'added_by' => user()->id,
                        'employee_shift_id' => $this->attendanceSettings->id,
                        'overwrite_attendance' => request()->has('overwrite_attendance') ? $request->overwrite_attendance : 'no',
                        'last_updated_by' => user()->id
                    ];
                }
            }
        }
        if ($holidayError) {
            return Reply::error(__('messages.holidayError')); // Display error message
        }

        Attendance::insertOrIgnore($insertData);

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('attendances.index');
        }

        return Reply::redirect($redirectUrl, __('messages.attendanceSaveSuccess'));
    }

    public function checkHalfDay(Request $request)
    {
        $halfDayDurationEnded = null; // Yes  ? not on half day &  No ? on half day

        if ($request->type == 'bulkMark') {
            $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $request->month . '-' . $request->year)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $period = CarbonPeriod::create($startDate, $endDate);

            $dates = [];

            foreach ($period as $date) {
                $dates[] = $date;
            }

            if ($request->has('user_id') && $request->user_id !== null) {
                $leaves = Leave::whereIn('user_id', $request->user_id)
                    ->whereIn('leave_date', $dates)
                    ->where('status', '!=', 'rejected')
                    ->whereIn('duration', ['half day', 'single', 'multiple'])
                    ->get();
            }

            if (isset($leaves) && $leaves->isNotEmpty()) {
                foreach ($leaves as $leave) {

                    if ($leave->duration === 'half day') {
                        $userShifts = User::findOrFail($leave->user_id)->shifts()->whereDate('date', $leave->leave_date)->get();
                        if ($userShifts->isNotEmpty()) {
                            $halfDayDurationEnded = $this->halfDayShiftCheck($leave, true, $userShifts);
                        } else {
                            $halfDayDurationEnded = $this->halfDayShiftCheck($leave, false);
                        }
                    }

                    $halfDay = $leave->duration == 'half day' ? 'no' : 'yes';
                    $this->halfDayExist = $leave->duration == 'half day';
                    $this->requestedHalfDay = $halfDay;
                    $this->halfDayDurEnd = $halfDayDurationEnded;

                    $fullDay = ($leave->duration == 'single' || $leave->duration == 'multiple') ? 'no' : 'yes';
                    $this->fullDayExist = $leave->duration == 'single' || $leave->duration == 'multiple';
                    $this->requestedFullDay = $fullDay;

                    $this->user = $leave->user->name;
                }
            }

             return reply::dataOnly($this->data);
        }
        else {
            $leave = Leave::where([
                ['user_id', $request->user_id],
                ['leave_date', $request->attendance_date]
            ])->where('status', '!=', 'rejected')
            ->whereIn('duration', ['half day', 'single', 'multiple'])->first();

            if (isset($leave)) {

                if ($leave->duration === 'half day') {
                    $userShifts = User::findOrFail($leave->user_id)->shifts()->whereDate('date', $leave->leave_date)->get();
                    if ($userShifts->isNotEmpty()) {
                        $halfDayDurationEnded = $this->halfDayShiftCheck($leave, true, $userShifts);
                    } else {
                        $halfDayDurationEnded = $this->halfDayShiftCheck($leave, false);
                    }
                }

                $halfDay = $leave->duration == 'half day' ? 'no' : 'yes';
                $this->halfDayExist = $leave->duration == 'half day' ? true : false;
                $this->requestedHalfDay = $halfDay;
                $this->halfDayDurEnd = $halfDayDurationEnded;

                $fullDay = ($leave->duration == 'single' || $leave->duration == 'multiple') ? 'no' : 'yes';
                $this->fullDayExist = ($leave->duration == 'single' || $leave->duration == 'multiple') ? true : false;
                $this->requestedFullDay = $fullDay;

                $this->user = $leave->user->name;

            }

            return reply::dataOnly($this->data);
        }

    }

    public function halfDayShiftCheck($leave, $shiftFound, $userShifts = null) {

        if ($shiftFound) {
            foreach ($userShifts as $userShift) {

                $halfMarkTime = Carbon::createFromFormat('H:i:s', $userShift->shift->halfday_mark_time, $this->company->timezone);
                $currentTime = Carbon::now($this->company->timezone)->format('H:i:s');
                $currentTimeCarbon = Carbon::createFromFormat('H:i:s', $currentTime, $this->company->timezone);

                if ($leave->half_day_type == 'first_half') {
                    $halfDayDurationEnded = ($currentTimeCarbon->greaterThan($halfMarkTime)) ? 'yes' : 'no' ;

                } elseif ($leave->half_day_type == 'second_half') {
                    $halfDayDurationEnded = ($currentTimeCarbon->lessThan($halfMarkTime)) ? 'yes' : 'no' ;

                }
            }

        }else{
            $attendanceSetting = AttendanceSetting::first();
            $defaultShiftId = $attendanceSetting->default_employee_shift;
            $defaultShift = EmployeeShift::findOrFail($defaultShiftId);

            $halfMarkTime = Carbon::createFromFormat('H:i:s', $defaultShift->halfday_mark_time, $this->company->timezone);
            $currentTime = Carbon::now($this->company->timezone)->format('H:i:s');
            $currentTimeCarbon = Carbon::createFromFormat('H:i:s', $currentTime, $this->company->timezone);

            if ($leave->half_day_type == 'first_half') {
                $halfDayDurationEnded = ($currentTimeCarbon->greaterThan($halfMarkTime)) ? 'yes' : 'no' ;
            } elseif ($leave->half_day_type == 'second_half' && $currentTimeCarbon->lessThan($halfMarkTime)) {
                $halfDayDurationEnded = ($currentTimeCarbon->lessThan($halfMarkTime)) ? 'yes' : 'no' ;
            }
        }

        return $halfDayDurationEnded;
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $deleteAttendancePermission = user()->permission('delete_attendance');

        abort_403(!($deleteAttendancePermission == 'all' || ($deleteAttendancePermission == 'added' && $attendance->added_by == user()->id)));
        Attendance::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function importAttendance()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.attendance');

        $addPermission = user()->permission('add_attendance');

        abort_403(!($addPermission == 'all' || $addPermission == 'added'));


        if (request()->ajax()) {
            $html = view('attendances.ajax.import', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'attendances.ajax.import';

        return view('attendances.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, AttendanceImport::class);

        if($rvalue == 'abort'){
            return Reply::error(__('messages.abortAction'));
        }

        $view = view('attendances.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, AttendanceImport::class, ImportAttendanceJob::class);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    public function exportAttendanceByMember($year, $month, $id)
    {
        abort_403(!canDataTableExport());

        $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $month . '-' . $year)->startOfMonth()->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        $obj = User::findOrFail($id);
        $date = $endDate->lessThan(now()) ? $endDate : now();

        return Excel::download(new AttendanceByMemberExport($year, $month, $id, $obj->name, $startDate, $endDate), $obj->name . '_' . $startDate->format('d-m-Y') . '_To_' . $date->format('d-m-Y') . '.xlsx');
    }

    public function exportAllAttendance($year, $month, $id, $department, $designation)
    {
        abort_403(!canDataTableExport());

        $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $month . '-' . $year)->startOfMonth()->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $date = $endDate->lessThan(now()) ? $endDate : now();

        return Excel::download(new AttendanceExport($year, $month, $id, $department, $designation, $startDate, $endDate), 'Attendance_From_' . $startDate->format('d-m-Y') . '_To_' . $date->format('d-m-Y') . '.xlsx');
    }

    public function byHour(Request $request)
    {
        $this->pageTitle = 'modules.attendance.attendanceByHour';

        abort_403(!(in_array($this->viewAttendancePermission, ['all', 'added', 'owned', 'both'])));

        if (request()->ajax()) {
            return $this->hourSummaryData($request);
        }

        $users = User::with('employeeDetails')->join('employee_details', 'users.id', '=', 'employee_details.user_id');

        if ($this->viewAttendancePermission == 'owned') {
            $this->employees = $users->where(function ($query) {
                                    $query->where('employee_details.user_id', user()->id);
                                })->get();
        }
        elseif ($this->viewAttendancePermission == 'added') {
            $this->employees = $users->where(function ($query) {
                                        $query->where('employee_details.added_by', user()->id);
                                    })->get();
        }
        elseif ($this->viewAttendancePermission == 'both') {
            $this->employees = $users->where(function ($query) {
                                    $query->where('employee_details.user_id', user()->id)
                                        ->orWhere('employee_details.added_by', user()->id);
                                    })->get();
        }
        elseif ($this->viewAttendancePermission == 'all') {
            $this->employees = User::allEmployees(null, false, ($this->viewAttendancePermission == 'all' ? 'all' : null));
        }

        $now = now(company()->timezone);
        $this->year = $now->format('Y');
        $this->month = $now->format('m');
        $this->departments = Team::all();
        $this->designations = Designation::all();

        return view('attendances.by_hour', $this->data);
    }

    public function hourSummaryData($request)
    {
        $employees = User::with(
            [
                'employeeDetail.designation:id,name',
                'attendance' => function ($query) use ($request) {
                    $query->whereRaw('MONTH(attendances.clock_in_time) = ?', [$request->month])
                        ->whereRaw('YEAR(attendances.clock_in_time) = ?', [$request->year]);

                    if ($this->viewAttendancePermission == 'added') {
                        $query->where('attendances.added_by', user()->id);

                    }
                    elseif ($this->viewAttendancePermission == 'owned') {
                        $query->where('attendances.user_id', user()->id);
                    }
                },
                'leaves' => function ($query) use ($request) {
                    $query->whereRaw('MONTH(leaves.leave_date) = ?', [$request->month])
                        ->whereRaw('YEAR(leaves.leave_date) = ?', [$request->year])
                        ->where('status', 'approved');
                },
                'shifts' => function ($query) use ($request) {
                    $query->whereRaw('MONTH(employee_shift_schedules.date) = ?', [$request->month])
                        ->whereRaw('YEAR(employee_shift_schedules.date) = ?', [$request->year]);
                },
                'leaves.type', 'shifts.shift']
        )->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.id', 'users.name','users.inactive_date', 'users.email', 'users.created_at', 'employee_details.department_id', 'users.image')
            ->onlyEmployee()->withoutGlobalScope(ActiveScope::class)
            ->where(function ($query) use ($request) {
                $query->whereNull('users.inactive_date')
                      ->orWhere(function ($subQuery) use ($request) {
                          $subQuery->whereRaw('YEAR(users.inactive_date) >= ?', [$request->year])
                                ->whereRaw('MONTH(users.inactive_date) >= ?', [$request->month]);
                      });
            })
            ->groupBy('users.id');

        if ($request->department != 'all') {
            $employees = $employees->where('employee_details.department_id', $request->department);
        }

        if ($request->designation != 'all') {
            $employees = $employees->where('employee_details.designation_id', $request->designation);
        }

        if ($request->userId != 'all') {
            $employees = $employees->where('users.id', $request->userId);
        }

        if ($this->viewAttendancePermission == 'owned') {
            $employees = $employees->where('users.id', user()->id);
        }
        elseif ($this->viewAttendancePermission == 'both') {
            $employees = $employees->where('employee_details.user_id', user()->id)
                                ->orWhere('employee_details.added_by', user()->id);
        }
        elseif ($this->viewAttendancePermission == 'added') {
            $employees = $employees->where('employee_details.added_by', user()->id);
        }

        $employees = $employees->get();

        $this->holidays = Holiday::whereRaw('MONTH(holidays.date) = ?', [$request->month])->whereRaw('YEAR(holidays.date) = ?', [$request->year])->get();

        $final = [];
        $holidayOccasions = [];
        $total = [];

        $leaveReasons = [];
        $this->daysInMonth = Carbon::parse('01-' . $request->month . '-' . $request->year)->daysInMonth;
        $now = now()->timezone($this->company->timezone);
        $requestedDate = Carbon::parse(Carbon::parse('01-' . $request->month . '-' . $request->year))->endOfMonth();

        foreach ($employees as $count => $employee) {

            $dataBeforeJoin = null;

            $dataTillToday = array_fill(1, $now->copy()->format('d'), 'Absent');
            $dataTillRequestedDate = array_fill(1, (int)$this->daysInMonth, 'Absent');
            $daysTofill = ((int)$this->daysInMonth - (int)$now->copy()->format('d'));

            if (($now->copy()->format('d') != $this->daysInMonth) && !$requestedDate->isPast()) {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), (($daysTofill >= 0 ? $daysTofill : 0)), '-');
            }
            else {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), (($daysTofill >= 0 ? $daysTofill : 0)), 'Absent');
            }

            if (!$requestedDate->isPast()) {
                $final[$employee->id . '#' . $employee->name] = array_replace($dataTillToday, $dataFromTomorrow);

            }
            else {
                $final[$employee->id . '#' . $employee->name] = array_replace($dataTillRequestedDate, $dataFromTomorrow);
            }

            $totalMinutes = 0;

            $previousdate = null;

            foreach ($employee->shifts as $shifts) {
                if ($shifts->shift->shift_name == 'Day Off') {
                    $final[$employee->id . '#' . $employee->name][$shifts->date->day] = 'Day Off';
                }

            }

            foreach ($employee->attendance as $index => $attendance) {

                $from = $attendance->clock_in_time?->timezone(company()->timezone);

                $defaultEndDateAndTime = Carbon::createFromFormat('Y-m-d H:i:s', $from?->format('Y-m-d') . ' ' . attendance_setting()->shift->office_end_time, company()->timezone);

                $to = $attendance->clock_out_time ?: $defaultEndDateAndTime;

                // totalTime() function is used to calculate the total time of an employee on particular date
                $diffInMins = ($to && $from) ? $attendance->totalTime($from, $to, $employee->id, 'm') : 0;

                // previous date is used to store the previous date of an attendance, so that we can calculate the total time of an employee on particular date
                if ($index == 0) {
                    $previousdate = $from->format('Y-m-d');
                    $totalMinutes += $diffInMins;

                }
                elseif ($previousdate != $from->format('Y-m-d')) {
                    $totalMinutes += $diffInMins;
                    $previousdate = $from->format('Y-m-d');
                }

                if($diffInMins === 0){
                    $mins = $totalMinutes;
                }else{
                    $mins = $diffInMins;
                }
                $final[$employee->id . '#' . $employee->name][Carbon::parse($attendance->clock_in_time)->timezone($this->company->timezone)->day] = '<a href="javascript:;" class="view-attendance" data-attendance-id="' . $attendance->id . '">' . intdiv($mins, 60) . ':' . ($mins % 60) . '</a>';
            }

            // Convert minutes to hours
            /** @phpstan-ignore-next-line */
            $resultTotalTime = CarbonInterval::formatHuman($totalMinutes);

            $total[$count] = $resultTotalTime;

            $emplolyeeName = view('components.employee', [
                'user' => $employee
            ]);

            $final[$employee->id . '#' . $employee->name][] = $emplolyeeName;

            if ($employee->employeeDetail->joining_date->greaterThan(Carbon::parse(Carbon::parse('01-' . $request->month . '-' . $request->year)))) {
                if ($request->month == $employee->employeeDetail->joining_date->format('m') && $request->year == $employee->employeeDetail->joining_date->format('Y')) {
                    if ($employee->employeeDetail->joining_date->format('d') == '01') {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->format('d'), '-');
                    }
                    else {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->subDay()->format('d'), '-');
                    }
                }

                if (($request->month < $employee->employeeDetail->joining_date->format('m') && $request->year == $employee->employeeDetail->joining_date->format('Y')) || $request->year < $employee->employeeDetail->joining_date->format('Y')) {
                    $dataBeforeJoin = array_fill(1, $this->daysInMonth, '-');
                }
            }

            if (Carbon::parse('01-' . $request->month . '-' . $request->year)->isFuture()) {
                $dataBeforeJoin = array_fill(1, $this->daysInMonth, '-');
            }

            if (!is_null($dataBeforeJoin)) {
                $final[$employee->id . '#' . $employee->name] = array_replace($final[$employee->id . '#' . $employee->name], $dataBeforeJoin);
            }

            foreach ($employee->leaves as $leave) {
                if ($leave->duration == 'half day') {
                    if ($final[$employee->id . '#' . $employee->name][$leave->leave_date->day] == '-' || $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] == 'Absent') {
                        $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] = 'Half Day';
                    }
                }
                else {
                    $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] = 'Leave';
                    $leaveReasons[$employee->id][$leave->leave_date->day] = $leave->type->type_name . ': ' . $leave->reason;
                }

            }

            foreach ($employee->shifts as $shifts) {
                if ($shifts->shift->shift_name == 'Day Off') {
                    $final[$employee->id . '#' . $employee->name][$shifts->date->day] = 'Day Off';
                }

            }

            foreach ($this->holidays as $holiday) {
                $departmentId = $employee->employeeDetail->department_id;
                $designationId = $employee->employeeDetail->designation_id;
                $employmentType = $employee->employeeDetail->employment_type;


                $holidayDepartment = (!is_null($holiday->department_id_json)) ? json_decode($holiday->department_id_json) : [];
                $holidayDesignation = (!is_null($holiday->designation_id_json)) ? json_decode($holiday->designation_id_json) : [];
                $holidayEmploymentType = (!is_null($holiday->employment_type_json)) ? json_decode($holiday->employment_type_json) : [];

                if (((in_array($departmentId, $holidayDepartment) || $holiday->department_id_json == null) &&
                    (in_array($designationId, $holidayDesignation) || $holiday->designation_id_json == null) &&
                    (in_array($employmentType, $holidayEmploymentType) || $holiday->employment_type_json == null))
                ) {


                    if ($final[$employee->id . '#' . $employee->name][$holiday->date->day] == 'Absent' || $final[$employee->id . '#' . $employee->name][$holiday->date->day] == '-') {
                        $final[$employee->id . '#' . $employee->name][$holiday->date->day] = 'Holiday';
                        $holidayOccasions[$holiday->date->day] = $holiday->occassion;
                    }
                }
            }
        }

        $this->employeeAttendence = $final;
        $this->holidayOccasions = $holidayOccasions;
        $this->total = $total;
        $this->month = $request->month;
        $this->year = $request->year;
        $this->leaveReasons = $leaveReasons;

        $view = view('attendances.ajax.hour_summary_data', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view]);
    }

    public function byMapLocation(Request $request)
    {
        abort_403(!(in_array($this->viewAttendancePermission, ['all', 'added', 'owned', 'both'])));

        if (request()->ajax()) {
            return $this->byMapLocationData($request);
        }

        $this->employees = User::allEmployees(null, true, ($this->viewAttendancePermission == 'all' ? 'all' : null));
        $this->departments = Team::all();

        return view('attendances.by_map_location', $this->data);
    }

    protected function byMapLocationData($request)
    {
        $this->attendances = Attendance::with('user')
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('attendances.*')
            ->whereDate('clock_in_time', companyToDateString($request->attendance_date))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->department != 'all') {
            $this->attendances = $this->attendances->where('employee_details.department_id', $request->department);
        }

        if ($request->userId != 'all') {
            $this->attendances = $this->attendances->where('users.id', $request->userId);
        }

        if ($this->viewAttendancePermission == 'owned') {
            $this->attendances = $this->attendances->where('users.id', user()->id);
        }

        if ($request->late != 'all') {
            $this->attendances = $this->attendances->where('attendances.late', $request->late);
        }

        $this->attendances = $this->attendances->get();

        $view = view('attendances.ajax.map_location_data', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view]);
    }

    public function attendanceShift($defaultAttendanceSettings, $userId, $date, $clockInTime)
    {
        $checkPreviousDayShift = EmployeeShiftSchedule::without('shift')->where('user_id', $userId)
            ->where('date', $date->copy()->subDay()->toDateString())
            ->first();

        $checkTodayShift = EmployeeShiftSchedule::without('shift')->where('user_id', $userId)
            ->where('date', $date->copy()->toDateString())
            ->first();

        $backDayFromDefault = Carbon::parse($date->copy()->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_start_time);

        $backDayToDefault = Carbon::parse($date->copy()->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_end_time);

        if ($backDayFromDefault->gt($backDayToDefault)) {
            $backDayToDefault->addDay();
        }

        $nowTime = Carbon::createFromFormat('Y-m-d' . ' ' . company()->time_format, $date->copy()->toDateString() . ' ' . $clockInTime, 'UTC');

        if ($checkPreviousDayShift && $nowTime->betweenIncluded($checkPreviousDayShift->shift_start_time, $checkPreviousDayShift->shift_end_time)) {
            $attendanceSettings = $checkPreviousDayShift;

        }
        else if ($nowTime->betweenIncluded($backDayFromDefault, $backDayToDefault)) {
            $attendanceSettings = $defaultAttendanceSettings;

        }
        else if ($checkTodayShift &&
            ($nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time) || $nowTime->gt($checkTodayShift->shift_end_time))
        ) {
            $attendanceSettings = $checkTodayShift;

        }
        else if ($checkTodayShift && $checkTodayShift->shift->shift_type == 'flexible')
        {
            $attendanceSettings = $checkTodayShift;
        }
        else {
            $attendanceSettings = $defaultAttendanceSettings;
        }

        return $attendanceSettings->shift;

    }

    public function addAttendance($userID, $day, $month, $year)
    {
        $this->date = Carbon::createFromFormat('d-m-Y', $day . '-' . $month . '-' . $year)->format('Y-m-d');
        $this->attendance = Attendance::whereUserId($userID)->first();

        $attendanceSettings = EmployeeShiftSchedule::where('user_id', $userID)->where('date', $this->date)->first();

        if ($attendanceSettings) {
            $this->attendanceSettings = $attendanceSettings->shift;

        }
        else {
            $this->attendanceSettings = attendance_setting(); // Do not get this from session here

        }

        $this->location = CompanyAddress::all();
        $this->attendanceUser = User::findOrFail($userID);

        return view('attendances.ajax.add_user_attendance', $this->data);
    }

    public function qrCodeStatus(Request $request)
    {

        $attendanceSetting = AttendanceSetting::first();

        if (request()->qr_status == 1 && $attendanceSetting->auto_clock_in == 'yes') {
            return Reply::error(__('messages.qrCodeAndFirstSignError'));
        }

        $attendanceSetting->qr_enable = $request->qr_status;
        $attendanceSetting->save();

        return Reply::success('Success');
    }

    public function qrClockInOut(Request $request, $hash)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Please log in to clock in.');
        }

        // Retrieve the authenticated user
        $user = user();

        $leaveApplied = Leave::where('user_id', $user->id)
        ->whereDate('leave_date', now()->format('Y-m-d'))
        ->where('status', 'approved')
        ->first();

        $checkTodayHoliday = Holiday::where('date', now()->format('Y-m-d'))
            ->where(function ($query) use ($user) {
                $query->orWhere('department_id_json', 'like', '%"' . $user->employeeDetail->department_id . '"%')
                    ->orWhereNull('department_id_json');
            })
            ->where(function ($query) use ($user) {
                $query->orWhere('designation_id_json', 'like', '%"' . $user->employeeDetail->designation_id . '"%')
                    ->orWhereNull('designation_id_json');
            })
            ->where(function ($query) use ($user) {
                if (!is_Null($user->employeeDetail->employment_type)) {
                    $query->orWhere('employment_type_json', 'like', '%"' . $user->employeeDetail->employment_type . '"%')
                        ->orWhereNull('employment_type_json');
                }
            })
            ->first();

        $company = company();

        $showClockIn = attendance_setting();
        $attendanceSettings = $this->attendanceShiftFromTrait($showClockIn);

        $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceSettings->office_start_time, company()->timezone);
        $midTime = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceSettings->halfday_mark_time, company()->timezone);
        $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceSettings->office_end_time, company()->timezone);

        $currentTime = now()->timezone($company->timezone);

        if (!is_null($attendanceSettings->early_clock_in)) {
            $startTime->subMinutes($attendanceSettings->early_clock_in);
        }

        if ($startTime->gt($endTime)) { // check if shift end time is less then current time then shift not ended yet
            if(
                now(company()->timezone)->lessThan($endTime)
                || (now(company()->timezone)->greaterThan($endTime) && now(company()->timezone)->lessThan($startTime))
            ){
                $startTime->subDay();
                $midTime->subDay();
            }else{
                $midTime->addDay();
                $endTime->addDay();
            }
        }

        $cannotLogin = false;
        $date = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $this->user->id)
            ->whereDate('clock_in_time', $date)
            ->get();

        foreach ($attendance as $item) {
            if ($item->clock_out_time !== null && now()->between($item->clock_in_time, $item->clock_out_time)) { // if ($item->clock_out_time !== null && added this condition
                $cannotLogin = true;
                break;
            }
        }

        if ($showClockIn->employee_clock_in_out == 'no' || $attendanceSettings->shift_name == 'Day Off') {

            $cannotLogin = true;
        }
        elseif (is_null($attendanceSettings->early_clock_in) && !now($this->company->timezone)->between($startTime, $endTime) && $showClockIn->show_clock_in_button == 'no' && $attendanceSettings->shift_type == 'strict') {
            $cannotLogin = true;

        }
        elseif($attendanceSettings->shift_type == 'strict') {
            $earlyClockIn = now($this->company->timezone)->addMinutes($attendanceSettings->early_clock_in)->setTimezone('UTC');

            if (!$earlyClockIn->gte($startTime) && $showClockIn->show_clock_in_button == 'no') {

                $cannotLogin = true;
            }
            elseif ($cannotLogin && now()->betweenIncluded($startTime->copy()->subDay(), $endTime->copy()->subDay())) {
                $cannotLogin = false;

            }
        }
        $isAttendanceApplied = true;

        if ($leaveApplied != null  && ($leaveApplied->duration === 'single' || $leaveApplied->duration === 'multiple') && $currentTime->between($startTime, $endTime)) {

            $message = __('messages.leaveApplied');
            $clockInTime = null;
            $clockOutTime = null;
            $totalWorkingTime = null;
            $leave = true;
            $isAttendanceApplied = false;
            return view('attendance-settings.ajax.qrview', compact('message', 'leave'));

        }elseif ($leaveApplied != null  && ($leaveApplied->duration === 'half day' && $leaveApplied->half_day_type === 'first_half' && $currentTime->lt($midTime) && $currentTime->between($startTime, $midTime))) {

            $message = __('messages.leaveForFirstHalf');
            $clockInTime = null;
            $clockOutTime = null;
            $totalWorkingTime = null;
            $leave = true;
            $isAttendanceApplied = false;
            return view('attendance-settings.ajax.qrview', compact('message', 'leave'));

        }elseif ($leaveApplied != null  && ($leaveApplied->duration === 'half day' && $leaveApplied->half_day_type === 'second_half' && $currentTime->gt($midTime) && $currentTime->between($midTime, $endTime))) {

            $message = __('messages.leaveForSecondHalf');
            $clockInTime = null;
            $clockOutTime = null;
            $totalWorkingTime = null;
            $leave = true;
            $isAttendanceApplied = false;
            return view('attendance-settings.ajax.qrview', compact('message', 'leave'));

        }elseif(!is_null($checkTodayHoliday) && $checkTodayHoliday->exists){

            $message = __('messages.todayHoliday');
            $clockInTime = null;
            $clockOutTime = null;
            $totalWorkingTime = null;
            $holiday = true;
            $isAttendanceApplied = false;
            return view('attendance-settings.ajax.qrview', compact('message', 'holiday'));

        } elseif($cannotLogin == true){

            $message = __('messages.notInOfficeHours');
            $clockInTime = null;
            $clockOutTime = null;
            $totalWorkingTime = null;
            $outOfShiftHours = true;
            $isAttendanceApplied = false;
            return view('attendance-settings.ajax.qrview', compact('message', 'outOfShiftHours'));

        }

        $userCompany = Company::where('hash', $hash)->first();

        // Check if the user is already clocked in for today
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('clock_in_time', now()->format('Y-m-d'))
            ->where('company_id', $userCompany->id)
            ->whereNull('clock_out_time')
            ->first();

        $outtimeDate = null;
        $intimeDate = null;

        $trackerReminder = LogTimeFor::where('company_id', $company->id)->first();

        $time = $currentTime->format('h:i A');

        if ($todayAttendance && !session('qr_clock_in')) {
            // User is already clocked in, so clock them out
            $this->clockOutUser($todayAttendance);
            $message = __('messages.attendanceClockOutSuccess');
            $clockInTime = $todayAttendance->clock_in_time;
            $intime = Carbon::parse($clockInTime)->timezone($company->timezone);
            $intimeDate = $intime->format('h:i A');

            $clockOutTime = $todayAttendance->clock_out_time;
            $outtime = Carbon::parse($clockOutTime)->timezone($company->timezone);
            $outtimeDate = $outtime->format('h:i A');

            $workingintime = Carbon::parse($clockInTime); // Assuming $clockInTime is already a Carbon instance
            $workingouttime = Carbon::parse($todayAttendance->clock_out_time); // Assuming $todayAttendance->clock_out_time is already a Carbon instance

            $totalWorkingTime = $workingintime->diff($workingouttime)->format('%h hours %i minutes');
            $notAuthorize = false;
            $qrClockIn = false;
        }
        else {
            $attendanceSetting = AttendanceSetting::first();
            // Check if attendance setting status is enabled
            if ($attendanceSetting->qr_enable == 1) {
                // User is not clocked in for today, so clock them in
                if($isAttendanceApplied = true){
                    $res = $this->clockInUser($user, $request);
                    session(['qr_clock_in' => true]);
                }
                if (isset($res['status']) && $res['status'] == 'success') {
                    // Clock in successful, set success message
                    $message = __('messages.attendanceClockInSuccess');
                    $clockInTime = $todayAttendance?->clock_in_time->format('h:i A');
                    $clockOutTime = null;
                    $totalWorkingTime = null;
                    $notAuthorize = false;
                    $qrClockIn = false;
                }
                elseif (isset($res['type']) && $res['type'] == 'error') {
                    // Clock in successful, set success message
                    $message = $res['message'] ?? __('messages.attendanceClockInFailed');
                    $clockInTime = null;
                    $clockOutTime = null;
                    $totalWorkingTime = null;
                    $notAuthorize = true;
                    $qrClockIn = true;
                    $time = null;
                }
                else {
                    // Clock in failed, set error message
                    $message = $res['message'] ?? __('messages.attendanceClockInFailed');
                    $clockInTime = null;
                    $clockOutTime = null;
                    $totalWorkingTime = null;
                    $time = null;
                    $notAuthorize = false;
                    $qrClockIn = false;
                }
            }
            else {
                // Attendance feature is not enabled, prevent user from clocking in
                return redirect()->route('dashboard')->with('error', __('Attendance feature is currently disabled.'));
            }
        }

        // Pass the message and other details to the view and return the view
        return view('attendance-settings.ajax.qrview', compact('message', 'intimeDate', 'outtimeDate', 'totalWorkingTime', 'todayAttendance', 'time', 'notAuthorize', 'qrClockIn'));

    }

    private function clockInUser($user, $request)
    {
        $now = now();
        $showClockIn = AttendanceSetting::first();

        // Retrieve attendance settings
        $this->attendanceSettings = $this->attendanceShiftqr($showClockIn);

        // Construct start and end timestamps
        $startTimestamp = now()->format('Y-m-d') . ' ' . $this->attendanceSettings->office_start_time;
        $endTimestamp = now()->format('Y-m-d') . ' ' . $this->attendanceSettings->office_end_time;
        $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTimestamp, $this->company->timezone);
        $officeEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTimestamp, $this->company->timezone);

        // Check if the user can clock in
        if ($showClockIn->show_clock_in_button == 'yes') {
            $officeEndTime = now();
        }
        $officeStartTime = $officeStartTime->setTimezone('UTC');
        $officeEndTime = $officeEndTime->setTimezone('UTC');
        if ($officeStartTime->gt($officeEndTime)) {
            $officeEndTime->addDay();
        }

        $this->cannotLogin = false;
        $clockInCount = Attendance::getTotalUserClockInWithTime($officeStartTime, $officeEndTime, $this->user->id);

        // Adjust timestamps based on office start and end times
        // $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTimestamp, $this->company->timezone)
        //     ->setTimezone('UTC');
        // $officeEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTimestamp, $this->company->timezone)
        //     ->setTimezone('UTC');
        //     $lateTime = $officeStartTime->addMinutes($this->attendanceSettings->late_mark_duration);
        //     $checkTodayAttendance = Attendance::where('user_id', $this->user->id)
        //     ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $now->format('Y-m-d'))->first();


        // Check if the user is allowed to clock in based on settings
        if ($showClockIn->employee_clock_in_out == 'yes') {

            // Check if it's too early to clock in
            if (is_null($this->attendanceSettings->early_clock_in) && !$now->between($officeStartTime, $officeEndTime) && $showClockIn->show_clock_in_button == 'no' && $this->attendanceSettings->shift_type == 'strict') {
                $this->cannotLogin = true;
            }
            elseif($this->attendanceSettings->shift_type == 'strict') {
                $earlyClockIn = now(company()->timezone)->addMinutes($this->attendanceSettings->early_clock_in);
                $earlyClockIn = $earlyClockIn->setTimezone('UTC');

                if ($earlyClockIn->gte($officeStartTime) || $showClockIn->show_clock_in_button == 'yes') {
                    $this->cannotLogin = false;
                }
                else {
                    $this->cannotLogin = true;
                }
            }

            // Check if the user can clock in from previous day
            if ($this->cannotLogin && now()->betweenIncluded($officeStartTime->copy()->subDay(), $officeEndTime->copy()->subDay())) {
                $this->cannotLogin = false;
                $clockInCount = Attendance::getTotalUserClockInWithTime($officeStartTime->copy()->subDay(), $officeEndTime->copy()->subDay(), $this->user->id);

            }
        }
        else {
            $this->cannotLogin = true;
        }

        // Abort if user cannot login
        abort_403($this->cannotLogin);
        // Check user by IP
        $notAuthorize = false;
        if (attendance_setting()->ip_check == 'yes') {
            $ips = (array)json_decode(attendance_setting()->ip_address);
            if (!in_array($request->ip(), $ips)) {
                return ['type' => 'error', 'message' => __('messages.notAnAuthorisedDevice'), 'notAuthorize' => $notAuthorize, 'time' => null];
            }else{
                $notAuthorize = true;
            }
        }
        $qrClockIn = false;
        $currentLatitude = $currentLongitude = null;

        // Check user by location
        if (attendance_setting()->radius_check == 'yes') {
            $checkRadius = $this->isWithinRadius($request, $user);
            if (!$checkRadius) {
                return ['type' => 'error', 'message' => __('messages.notAnValidLocation'), 'qrClockIn' => $qrClockIn];
            }else{
                $qrClockIn = true;
                $currentLatitude = $request->currentLatitude ?: session('current_latitude');
                $currentLongitude = $request->currentLongitude ?: session('current_longitude');
            }
        }

        // Check maximum attendance in a day
        if ($this->attendanceSettings->shift_type == 'strict') {
            $clockInCount = Attendance::getTotalUserClockInWithTime($officeStartTime, $officeEndTime, $user->id);

        } else {
            $clockInCount = Attendance::whereDate('clock_in_time', $officeStartTime->copy()->toDateString())
            ->where('user_id', $user->id)
            ->count();
        }


        if ($clockInCount >= $this->attendanceSettings->clockin_in_day) {
            return ['type' => 'error', 'message' => __('messages.maxClockin')];

            // return Reply::error(__('messages.maxClockin'));

        }
        if ($this->attendanceSettings->halfday_mark_time) {
            $halfDayTimestamp = $now->format('Y-m-d') . ' ' . $this->attendanceSettings->halfday_mark_time;
            $halfDayTimestamp = Carbon::createFromFormat('Y-m-d H:i:s', $halfDayTimestamp, $this->company->timezone);
            $halfDayTimestamp = $halfDayTimestamp->setTimezone('UTC');
            $halfDayTimestamp = $halfDayTimestamp->timestamp;
        }


        $timestamp = $now->format('Y-m-d') . ' ' . $this->attendanceSettings->office_start_time;
        $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $this->company->timezone);
        $officeStartTime = $officeStartTime->setTimezone('UTC');

        $lateTime = $officeStartTime->addMinutes($this->attendanceSettings->late_mark_duration);

        $checkTodayAttendance = Attendance::where('user_id', $this->user->id)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $now->format('Y-m-d'))->first();

        if ($user->employeeDetail && $user->employeeDetail->company_address_id) {
            $companyAddress = CompanyAddress::findOrFail($user->employeeDetail->company_address_id);
        } else {
            $companyAddress = CompanyAddress::where('is_default', 1)->where('company_id',$user->company_id)->first();
        }

        // Save the attendance record
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->clock_in_time = $now;
        $attendance->clock_in_ip = request()->ip();
        $attendance->location_id = $companyAddress->id;
        $attendance->work_from_type = 'office';
        $attendance->latitude = $currentLatitude;
        $attendance->longitude = $currentLongitude;

        // Add more attributes as necessary...
        if ($now->gt($lateTime)) {
            $attendance->late = 'yes';
        }

        $leave = Leave::where('leave_date', $attendance->clock_in_time->format('Y-m-d'))
            ->where('user_id', $this->user->id)->first();

        if (isset($leave) && !is_null($leave->half_day_type)) {
            $attendance->half_day = 'yes';
        }
        else {
            $attendance->half_day = 'no';
        }

        $currentTimestamp = $now->setTimezone('UTC');
        $currentTimestamp = $currentTimestamp->timestamp;;

        // Check day's first record and half day time
        if (
            !is_null($this->attendanceSettings->halfday_mark_time)
            && is_null($checkTodayAttendance)
            && isset($halfDayTimestamp)
            && ($currentTimestamp > $halfDayTimestamp)
            && ($showClockIn->show_clock_in_button == 'no') // DO NOT allow half day when allowed outside hours clock-in
        ) {
            $attendance->half_day = 'yes';
        }

        $currentLatitude = $request->currentLatitude;
        $currentLongitude = $request->currentLongitude;

        if ($currentLatitude != '' && $currentLongitude != '') {
            $attendance->latitude = $currentLatitude;
            $attendance->longitude = $currentLongitude;
        }

        $attendance->employee_shift_id = $this->attendanceSettings->id;

        $attendance->shift_start_time = $attendance->clock_in_time->toDateString() . ' ' . $this->attendanceSettings->office_start_time;

        if (Carbon::parse($this->attendanceSettings->office_start_time)->gt(Carbon::parse($this->attendanceSettings->office_end_time))) {
            $attendance->shift_end_time = $attendance->clock_in_time->addDay()->toDateString() . ' ' . $this->attendanceSettings->office_end_time;

        }
        else {
            $attendance->shift_end_time = $attendance->clock_in_time->toDateString() . ' ' . $this->attendanceSettings->office_end_time;
        }

        $attendance->save();

        return Reply::successWithData(__('messages.attendanceClockInSuccess'), ['time' => $now->format('h:i A'), 'ip' => $attendance->clock_in_ip, 'working_from' => $attendance->working_from, 'notAuthorize' => $notAuthorize, 'qrClockIn' => $qrClockIn]);


        // return ['type' => 'success', 'message' => __('messages.attendanceSaveSuccess')];

        // return Reply::successWithData(__('messages.attendanceSaveSuccess'), [
        //     'time' => $now->format('h:i A'),
        //     'ip' => $attendance->clock_in_ip,
        //     'working_from' => $attendance->working_from
        // ]);
    }

    private function clockOutUser($attendance)
    {
        // Update clock-out time
        $attendance->update(['clock_out_time' => now()]);
    }

    public function attendanceShiftqr($defaultAttendanceSettings)
    {
        $checkPreviousDayShift = EmployeeShiftSchedule::with('shift')->where('user_id', user()->id)
            ->where('date', now(company()->timezone)->subDay()->toDateString())
            ->first();

        $checkTodayShift = EmployeeShiftSchedule::with('shift')->where('user_id', user()->id)
            ->where('date', now(company()->timezone)->toDateString())
            ->first();

        $backDayFromDefault = Carbon::parse(now(company()->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_start_time);

        $backDayToDefault = Carbon::parse(now(company()->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_end_time);

        if ($backDayFromDefault->gt($backDayToDefault)) {
            $backDayToDefault->addDay();
        }

        $nowTime = Carbon::createFromFormat('Y-m-d H:i:s', now(company()->timezone)->toDateTimeString(), 'UTC');

        if ($checkPreviousDayShift && $nowTime->betweenIncluded($checkPreviousDayShift->shift_start_time, $checkPreviousDayShift->shift_end_time)) {
            $attendanceSettings = $checkPreviousDayShift;

        }
        else if ($nowTime->betweenIncluded($backDayFromDefault, $backDayToDefault)) {
            $attendanceSettings = $defaultAttendanceSettings;

        }
        else if ($checkTodayShift &&
            ($nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time)
                || $nowTime->gt($checkTodayShift->shift_end_time)
                || (!$nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time) && $defaultAttendanceSettings->show_clock_in_button == 'no'))
        ) {
            $attendanceSettings = $checkTodayShift;
        }
        else if ($checkTodayShift && !is_null($checkTodayShift->shift->early_clock_in)) {
            $attendanceSettings = $checkTodayShift;
        }
        else {
            $attendanceSettings = $defaultAttendanceSettings;
        }

        return $attendanceSettings->shift;

    }

}
