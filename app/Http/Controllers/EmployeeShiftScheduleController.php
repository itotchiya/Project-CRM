<?php

namespace App\Http\Controllers;

use App\Events\BulkShiftEvent;
use App\Exports\ShiftScheduleExport;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\EmployeeShift\StoreBulkShift;
use App\Models\AttendanceSetting;
use App\Models\EmailNotificationSetting;
use App\Models\EmployeeShift;
use App\Models\EmployeeShiftChangeRequest;
use App\Models\EmployeeShiftSchedule;
use App\Models\Holiday;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Company;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Scopes\ActiveScope;

class EmployeeShiftScheduleController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.shiftRoster';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('attendance', $this->user->modules));

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $this->viewShiftPermission = user()->permission('view_shift_roster');
        $this->manageEmployeeShifts = user()->permission('manage_employee_shifts');

        abort_403(!(in_array($this->viewShiftPermission, ['all', 'owned'])));

        if (request()->ajax()) {
            if (request()->view_type == 'week') {
                return $this->weekSummaryData($request);
            }

            return $this->summaryData($request);
        }

        $this->employeeShiftChangeRequest = EmployeeShiftChangeRequest::selectRaw('count(employee_shift_change_requests.id) as request_count')->where('employee_shift_change_requests.status', 'waiting')->first();

        if ($this->viewShiftPermission == 'owned') {
            $this->employees = User::where('id', user()->id)->get();

        }
        else {
            $this->employees = User::allEmployees(null, false, ($this->viewShiftPermission == 'all' ? 'all' : null));
        }

        $now = now();
        $this->year = $now->format('Y');
        $this->month = $now->format('m');
        $this->departments = Team::all();

        return view('shift-rosters.index', $this->data);
    }

    public function summaryData($request)
    {
        $this->attendanceSetting = AttendanceSetting::with('shift')->first()->shift;
        $this->employeeShifts = EmployeeShift::where('shift_name', '<>', 'Day Off')->get();
        $this->year = $request->change_year ?: $request->year;
        $this->month = $request->change_month ?: $request->month;

        $employees = User::with(
            [
                'shifts' => function ($query) {
                    $query->whereRaw('MONTH(employee_shift_schedules.date) = ?', [$this->month])
                        ->whereRaw('YEAR(employee_shift_schedules.date) = ?', [$this->year]);
                },
                'leaves' => function ($query) {
                    $query->whereRaw('MONTH(leaves.leave_date) = ?', [$this->month])
                        ->whereRaw('YEAR(leaves.leave_date) = ?', [$this->year])
                        ->where('status', 'approved');
                },
                'shifts.shift',
            ]
        )->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.inactive_date','users.id', 'users.name','users.status', 'users.email', 'users.created_at', 'employee_details.department_id', 'users.image')
            ->onlyEmployee()
            ->withoutGlobalScope(ActiveScope::class)
            ->where(function ($query) {
                // $query->whereNull('users.inactive_date')
                $query->where('users.status','active')
                      ->orWhere(function ($subQuery) {
                          $subQuery->whereRaw('YEAR(users.inactive_date) >= ?', [$this->year])
                                ->whereRaw('MONTH(users.inactive_date) >= ?', [$this->month]);
                      });
            })
            ->groupBy('users.id');

        if ($request->department != 'all') {
            $employees = $employees->where('employee_details.department_id', $request->department);
        }


        if ($request->userId != 'all') {
            $employees = $employees->where('users.id', $request->userId);
        }

        $employees = $employees->get();

        $this->holidays = Holiday::whereRaw('MONTH(holidays.date) = ?', [$this->month])->whereRaw('YEAR(holidays.date) = ?', [$this->year])->get();

        $final = [];
        $holidayOccasions = [];
        $shiftColorCode = [];

        $this->daysInMonth = Carbon::parse('01-' . $this->month . '-' . $this->year)->daysInMonth;
        $now = now()->timezone($this->company->timezone);
        $requestedDate = Carbon::parse(Carbon::parse('01-' . $this->month . '-' . $this->year))->endOfMonth();
        $deactiveEmp = [];

        foreach ($employees as $employee) {
            $isActive = '';

            if($employee->status == 'deactive'){
                $isActive = 'no';
                $deactiveEmp[] = $employee->id;
            }

            $dataBeforeJoin = null;

            $dataTillToday = array_fill(1, $requestedDate->copy()->format('d'), 'EMPTY');

            if (!$requestedDate->isPast()) {
                $dateTofill = ((int)$this->daysInMonth - (int)$now->copy()->format('d'));
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), (($dateTofill < 0) ? 0 : $dateTofill), 'EMPTY');
                $shiftColorCode = array_fill(1, ((int)$this->daysInMonth), $this->attendanceSetting->color);
            }
            else if ($requestedDate->isPast() && ((int)$this->daysInMonth - (int)$now->copy()->format('d')) < 0) {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), 0, 'EMPTY');
                $shiftColorCode = array_fill(1, ((int)$this->daysInMonth), $this->attendanceSetting->color);
            }
            else {
                $dateTofill = ((int)$this->daysInMonth - (int)$now->copy()->format('d'));
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), (($dateTofill < 0) ? 0 : $dateTofill), 'EMPTY');
                $shiftColorCode = array_fill(1, ((int)$this->daysInMonth), $this->attendanceSetting->color);
            }

            $final[$employee->id . '#' . $employee->name] = array_replace($dataTillToday, $dataFromTomorrow);

            foreach ($employee->shifts as $shift) {
                if ($shift->shift->shift_name == 'Day Off') {
                    $final[$employee->id . '#' . $employee->name][Carbon::parse($shift->date)->day] = '<button type="button" class="change-shift'.$isActive.' badge badge-light border f-10 p-1" data-user-id="' . $shift->user_id . '" data-attendance-date="' . $shift->date->day . '"  data-toggle="tooltip" data-original-title="' . __('modules.attendance.dayOff') . '" style="background-color: #E8EEF3">' . $shift->shift->shift_short_code . '</button>';
                    $shiftColorCode[Carbon::parse($shift->date)->day] = $shift->color;

                }
                else {
                    $final[$employee->id . '#' . $employee->name][Carbon::parse($shift->date)->day] = '<button type="button" class="change-shift'.$isActive.' badge badge-info f-10 p-1" style="background-color: ' . $shift->shift->color . '" data-user-id="' . $shift->user_id . '" data-attendance-date="' . $shift->date->day . '"  data-toggle="tooltip" data-original-title="' . $shift->shift->shift_name . '">' . $shift->shift->shift_short_code . '</button>';
                    $shiftColorCode[Carbon::parse($shift->date)->day] = $shift->color;
                }

            }

            $employeeName = view('components.employee', [
                'user' => $employee
            ]);

            $final[$employee->id . '#' . $employee->name][] = $employeeName;

            if ($employee->employeeDetail->joining_date->greaterThan(Carbon::parse(Carbon::parse('01-' . $this->month . '-' . $this->year)))) {
                if ($request->month == $employee->employeeDetail->joining_date->format('m') && $this->year == $employee->employeeDetail->joining_date->format('Y')) {
                    if ($employee->employeeDetail->joining_date->format('d') == '01') {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->format('d'), '-');
                        $shiftColorCode = array_fill(1, $employee->employeeDetail->joining_date->format('d'), '');
                    }
                    else {
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->subDay()->format('d'), '-');
                    }
                }

                if (($request->month < $employee->employeeDetail->joining_date->format('m') && $this->year == $employee->employeeDetail->joining_date->format('Y')) || $this->year < $employee->employeeDetail->joining_date->format('Y')) {
                    $dataBeforeJoin = array_fill(1, $this->daysInMonth, '-');
                }
            }

            if (!is_null($dataBeforeJoin)) {
                $final[$employee->id . '#' . $employee->name] = array_replace($final[$employee->id . '#' . $employee->name], $dataBeforeJoin);
            }

            foreach ($employee->leaves as $leave) {
                if ($leave->duration != 'half day') {
                    $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] = 'Leave';
                    $shiftColorCode[$leave->leave_date->day] = '';
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
                    (in_array($employmentType, $holidayEmploymentType) || $holiday->employment_type_json == null))) {

                    if ($final[$employee->id . '#' . $employee->name][$holiday->date->day] == 'Absent' || $final[$employee->id . '#' . $employee->name][$holiday->date->day] == 'EMPTY') {
                        $final[$employee->id . '#' . $employee->name][$holiday->date->day] = 'Holiday';
                        $holidayOccasions[$holiday->date->day] = $holiday->occassion;
                        $shiftColorCode[$holiday->date->day] = '';
                    }
                }
            }
        }

        $this->employeeAttendence = $final;
        $this->employeeIdsInactive = $deactiveEmp;
        $this->holidayOccasions = $holidayOccasions;
        $this->shiftColorCode = $shiftColorCode;
        $this->weekMap = Holiday::weekMap('D');

        $view = view('shift-rosters.ajax.summary_data', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view]);
    }

    public function weekSummaryData($request)
    {

        $this->attendanceSetting = AttendanceSetting::with('shift')->first()->shift;
        $this->employeeShifts = EmployeeShift::where('shift_name', '<>', 'Day Off')->get();

        $now = Carbon::parse($request->week_start_date, company()->timezone);
        $this->weekStartDate = $now->copy()->startOfWeek(attendance_setting()->week_start_from);
        $this->weekEndDate = $this->weekStartDate->copy()->addDays(6);
        $this->weekPeriod = CarbonPeriod::create($this->weekStartDate, $this->weekStartDate->copy()->addDays(6)); // Get All Dates from start to end date

        $employees = User::with(
            [
                'employeeDetail.designation:id,name',
                'shifts' => function ($query) {
                    $query->wherebetween('employee_shift_schedules.date', [$this->weekStartDate->toDateString(), $this->weekEndDate->toDateString()]);
                },
                'leaves' => function ($query) {
                    $query->wherebetween('leaves.leave_date', [$this->weekStartDate->toDateString(), $this->weekEndDate->toDateString()])
                        ->where('status', 'approved');
                }, 'shifts.shift', 'leaves.type']
        )->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.inactive_date','users.status','users.email', 'users.created_at', 'employee_details.department_id', 'users.image')
            ->withoutGlobalScope(ActiveScope::class)
            ->where(function ($query) use ($request) {
                // $query->whereNull('users.inactive_date')
                $query->where('users.status','active')
                      ->orWhere(function ($subQuery) use ($request) {
                          $subQuery->whereRaw('YEAR(users.inactive_date) >= ?', [$request->year])
                                ->whereRaw('MONTH(users.inactive_date) >= ?', [$this->weekStartDate->month])
                                ->orWhereRaw('MONTH(users.inactive_date) >= ?', [$this->weekEndDate->month]);
                            });
                        })
            ->onlyEmployee()->groupBy('users.id');

        if ($request->department != 'all') {
            $employees = $employees->where('employee_details.department_id', $request->department);
        }


        if ($request->userId != 'all') {
            $employees = $employees->where('users.id', $request->userId);
        }

        $employees = $employees->get();

        $this->holidays = Holiday::whereBetween('holidays.date', [$this->weekStartDate->toDateString(), $this->weekEndDate->toDateString()])->get();

        $final = [];
        $holidayOccasions = [];
        $leaveType = [];
        $shiftColorCode = [];
        $deactiveEmp = [];

        $this->daysInMonth = 7; // Week total days
        $now = now()->timezone($this->company->timezone);


        foreach ($employees as $employee) {
            $dataBeforeJoin = null;
            $isActive = '';

            if($employee->status == 'deactive'){
                $isActive = 'no';
                $deactiveEmp[] = $employee->id;
            }

            foreach ($this->weekPeriod->toArray() as $date) {
                $final[$employee->id . '#' . $employee->name][$date->toDateString()] = 'EMPTY';
            }

            foreach ($employee->shifts as $shift) {
                $dateString = Carbon::parse($shift->date)->toDateString();

                if ($shift->shift->shift_name == 'Day Off') {
                    $final[$employee->id . '#' . $employee->name][$dateString] = '<button type="button" class="change-shift-week'.$isActive.' badge as badge-light f-10 p-1 border f-12 py-4 w-100" data-user-id="' . $shift->user_id . '" data-attendance-date="' . $dateString . '" style="background-color: #E8EEF3"><div>' . __('modules.attendance.dayOff') . '<div></button>';
                    $shiftColorCode[$dateString] = $shift->color;

                } else {
                    $final[$employee->id . '#' . $employee->name][$dateString] = '<button type="button" class="change-shift-week'.$isActive.' badge ass badge-info text-left f-12 py-3 px-2 w-100" style="background-color: ' . $shift->shift->color . '" data-user-id="' . $shift->user_id . '" data-attendance-date="' . $dateString . '"><div>' . $shift->shift->shift_name . '<div>';

                    if ($shift->shift->shift_type == 'strict')
                    {
                        $final[$employee->id . '#' . $employee->name][$dateString] .= '<div class="mt-2 f-10">' . Carbon::parse($shift->shift->office_start_time)->format('H:i') . ' - ' . Carbon::parse($shift->shift->office_end_time)->format('H:i') . '</div>';

                    } else {
                        $final[$employee->id . '#' . $employee->name][$dateString] .= '<div class="mt-2 f-10">' . $shift->shift->flexible_total_hours.' '.__('app.hrs') . '</div>';
                    }

                    $final[$employee->id . '#' . $employee->name][$dateString] .= '</button>';
                    $shiftColorCode[$dateString] = $shift->color;
                }
            }

            $emplolyeeName = view('components.employee', [
                'user' => $employee
            ]);

            $final[$employee->id . '#' . $employee->name][] = $emplolyeeName;

            $joiningDate = Carbon::createFromFormat('Y-m-d', $employee->employeeDetail->joining_date->toDateString(), company()->timezone)->startOfDay();

            if ($joiningDate->greaterThan($this->weekEndDate)) {
                foreach ($this->weekPeriod->toArray() as $date) {
                    $final[$employee->id . '#' . $employee->name][$date->toDateString()] = '-';
                }
            }
            elseif ($joiningDate->greaterThan($this->weekStartDate) && $joiningDate->lessThan($this->weekEndDate)) {
                foreach ($this->weekPeriod->toArray() as $date) {
                    if ($date->lessThan($joiningDate)) {
                        $final[$employee->id . '#' . $employee->name][$date->toDateString()] = '-';
                    }
                }
            }

            foreach ($employee->leaves as $leave) {
                if ($leave->duration != 'half day') {
                    $final[$employee->id . '#' . $employee->name][$leave->leave_date->toDateString()] = 'Leave';
                    $shiftColorCode[$leave->leave_date->day] = '';
                    $leaveType[$employee->id][$leave->leave_date->toDateString()] = $leave->type->type_name;
                }
            }

            foreach ($this->holidays as $holiday) {
                $holidayDateString = $holiday->date->toDateString();
                $departmentId = $employee->employeeDetail->department_id;
                $designationId = $employee->employeeDetail->designation_id;
                $employmentType = $employee->employeeDetail->employment_type;

                $holidayDepartment = (!is_null($holiday->department_id_json)) ? json_decode($holiday->department_id_json) : [];
                $holidayDesignation = (!is_null($holiday->designation_id_json)) ? json_decode($holiday->designation_id_json) : [];
                $holidayEmploymentType = (!is_null($holiday->employment_type_json)) ? json_decode($holiday->employment_type_json) : [];

                if (((in_array($departmentId, $holidayDepartment) || $holiday->department_id_json == null) &&
                    (in_array($designationId, $holidayDesignation) || $holiday->designation_id_json == null) &&
                    (in_array($employmentType, $holidayEmploymentType) || $holiday->employment_type_json == null))) {

                    if ($final[$employee->id . '#' . $employee->name][$holidayDateString] == 'Absent' || $final[$employee->id . '#' . $employee->name][$holidayDateString] == 'EMPTY') {
                        $final[$employee->id . '#' . $employee->name][$holidayDateString] = 'Holiday';
                        $holidayOccasions[$holidayDateString] = $holiday->occassion;
                        $shiftColorCode[$holidayDateString] = '';
                    }
                }
            }

        }

        $this->employeeAttendence = $final;
        $this->employeeIdsInactive = $deactiveEmp;
        $this->holidayOccasions = $holidayOccasions;
        $this->leaveType = $leaveType;
        $this->shiftColorCode = $shiftColorCode;
        $this->weekMap = Holiday::weekMap('D');

        $view = view('shift-rosters.ajax.week_summary_data', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view]);
    }

    public function mark(Request $request, $userid, $day, $month, $year)
    {
        $manageEmployeeShifts = user()->permission('manage_employee_shifts');

        abort_403(!(in_array($manageEmployeeShifts, ['all'])));

        $this->date = Carbon::createFromFormat('d-m-Y', $day . '-' . $month . '-' . $year)->format('Y-m-d');
        $this->day = Carbon::createFromFormat('d-m-Y', $day . '-' . $month . '-' . $year)->dayOfWeek;

        $this->employee = User::findOrFail($userid);
        $this->shiftSchedule = EmployeeShiftSchedule::with('pendingRequestChange')->where('user_id', $userid)->where('date', $this->date)->first();
        $this->employeeShifts = EmployeeShift::all();

        return view('shift-rosters.ajax.edit', $this->data);
    }

    public function store(Request $request)
    {
        EmployeeShiftSchedule::firstOrCreate([
            'user_id' => $request->user_id,
            'date' => $request->shift_date,
            'employee_shift_id' => $request->employee_shift_id
        ]);

        return Reply::success(__('messages.employeeShiftAdded'));
    }

    public function update(Request $request, $id)
    {
        $shift = EmployeeShiftSchedule::findOrFail($id);
        $shift->employee_shift_id = $request->employee_shift_id;

        if (!$request->hasFile('file')) {
            Files::deleteFile($shift->file, 'employee-shift-file/' . $id);
            Files::deleteDirectory('employee-shift-file/' . $id);
            $shift->file = null;
        }

        if ($request->hasFile('file')) {
            Files::deleteFile($request->file, 'employee-shift-file/' . $id);
            $shift->file = Files::uploadLocalOrS3($request->file, 'employee-shift-file/' . $id);
        }

        $shift->save();

        return Reply::success(__('messages.employeeShiftAdded'));
    }

    public function destroy($id)
    {
        EmployeeShiftSchedule::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function exportAllShift($year, $month, $id, $department, $startDate, $viewType)
    {
        abort_403(!canDataTableExport());

        if ($viewType == 'week') {
            $now = Carbon::parse($startDate, company()->timezone);
            $startDate = $now->copy()->startOfWeek(attendance_setting()->week_start_from);
            $endDate = $startDate->copy()->addDays(6);
        }
        else {
            $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $month . '-' . $year)->startOfMonth()->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        }


        $date = $endDate->lessThan(now()) ? $endDate : now();

        return Excel::download(new ShiftScheduleExport($year, $month, $id, $department, $startDate, $endDate, $viewType), 'Attendance_From_' . $startDate->format('d-m-Y') . '_To_' . $date->format('d-m-Y') . '.xlsx');
    }

    public function employeeShiftCalendar(Request $request)
    {
        if (request('start') && request('end')) {
            $model = EmployeeShiftSchedule::with('shift')->where('user_id', $request->employeeId);

            $events = $model->get();

            $eventData = array();

            foreach ($events as $key => $event) {
                $startTime = Carbon::parse($event->date->toDateString() . ' ' . $event->shift->office_start_time);
                $endTime = Carbon::parse($event->date->toDateString() . ' ' . $event->shift->office_end_time);

                if ($startTime->gt($endTime)) {
                    $endTime->addDay();
                }

                $eventData[] = [
                    'id' => $event->id,
                    'userId' => $event->user_id,
                    'day' => $event->date->day,
                    'month' => $event->date->month,
                    'year' => $event->date->year,
                    'title' => $event->shift->shift_name,
                    'start' => $startTime,
                    'end' => $endTime,
                    'color' => $event->shift->color

                ];
            }

            return $eventData;

        }
    }

    public function create()
    {
        $this->employees = User::allEmployees(null, true, 'all');
        $this->departments = Team::all();
        $this->employeeShifts = EmployeeShift::all();
        $this->pageTitle = __('modules.attendance.bulkShiftAssign');
        $this->year = now()->format('Y');
        $this->month = now()->format('m');

        $dateFormat = Company::DATE_FORMATS;
        $this->dateformat = (isset($dateFormat[$this->company->date_format])) ? $dateFormat[$this->company->date_format] : 'DD-MM-YYYY';

        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'shift-assign-notification')->first();

        $this->view = 'shift-rosters.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('attendances.create', $this->data);

    }

    public function bulkShift(StoreBulkShift $request)
    {
        $employees = $request->user_id;
        $employeeData = User::with('employeeDetail')->whereIn('id', $employees)->get();
        $employeeShift = EmployeeShift::find($request->shift);
        $officeOpenDays = json_decode($employeeShift->office_open_days);

        $date = Carbon::createFromFormat('d-m-Y', '01-' . $request->month . '-' . $request->year)->format('Y-m-d');

        $period = [];
        $singleDate = '';
        $invalidShiftDays = false;
        $dayOfName = '';

        // Function to convert full month names to abbreviations
        $convertMonthToAbbreviation = function ($dateString) {
            $fullMonths = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            $abbreviatedMonths = [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ];

            return str_replace($fullMonths, $abbreviatedMonths, $dateString);
        };

        if ($request->assign_shift_by == 'month') {
            $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $request->month . '-' . $request->year)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $multiDates = CarbonPeriod::create($startDate, $endDate);
        }
        else if ($request->assign_shift_by == 'date') {
            $singleDate = Carbon::createFromFormat(company()->date_format, $request->single_date);
        } else {
            $dates = explode(',', $request->multi_date);
            $startDate = Carbon::parse($dates[0]);
            $endDate = Carbon::parse($dates[1]);
            $multiDates = CarbonPeriod::create($startDate, $endDate);
        }

        $dateRange = [];

        if ($request->assign_shift_by == 'multiple' || $request->assign_shift_by == 'month') {
            foreach ($multiDates as $multiDate) {
                $dateRange[] = $multiDate->format('Y-m-d');
            }
            foreach ($dateRange as $dateData) {
                $period[] = Carbon::parse($dateData);
            }
        }
        else {
            $period[] = Carbon::parse($singleDate);
        }

        $insertData = 0;

        $previousSchedules = EmployeeShiftSchedule::whereIn('user_id', $employees)
            ->whereIn('date', $dateRange)
            ->get();

        foreach ($previousSchedules as $previousSchedule) {
            if (!is_null($previousSchedule->file) || $previousSchedule->file != '') {
                Files::deleteFile($previousSchedule->file, 'employee-shift-file/' . $previousSchedule->id);
            }
            $previousSchedule->delete();
        }
        // $holidayError = false;

        foreach ($employees as $key => $userId) {
            $userData = $employeeData->where('id', $userId)->first();
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
                $formattedDate = $date->format('Y-m-d');
                $dayOfName = $date->dayName;
                // if (in_array($formattedDate, $holidaysForUser)) {
                //     if ($request->assign_shift_by == 'date') {
                //         $holidayError = true;
                //         break;
                //     }
                //     continue;
                // }

                if($officeOpenDays) {
                    if (!in_array($date->dayOfWeek, $officeOpenDays)) {
                        $invalidShiftDays = true;
                        continue;
                    }
                }

                if ($request->assign_shift_by != 'date') {
                    $this->bulkData($request, $date, $userData, $userId, $insertData, $officeOpenDays);
                }
                else {
                    $this->bulkData($request, $singleDate, $userData, $userId, $insertData, $officeOpenDays);
                }
            }
        }
        /* if ($holidayError) {
            return Reply::error(__('messages.holidayError')); // Display error message
        } */

        if ($invalidShiftDays && $request->assign_shift_by == 'date') {
            $errorMessage = __('messages.invalidShiftDayError') . ' ' . $dayOfName;
            return Reply::error($errorMessage);
        }

        if ($request->send_email && $insertData > 0) {
            foreach ($employees as $key => $userId) {
                $userData = $employeeData->where('id', $userId)->first();
                event(new BulkShiftEvent($userData, $dateRange, $userId));
            }
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('shifts.index');
        }

        return Reply::redirect($redirectUrl, __('messages.employeeShiftAdded'));
    }

    public function bulkData($request, $date, $userData, $userId, &$insertData, $officeOpenDays)
    {
        // Ensure $date is an instance of Carbon
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        if ($date->greaterThanOrEqualTo($userData->employeeDetail->joining_date) && (is_null($officeOpenDays) || (is_array($officeOpenDays) && in_array($date->dayOfWeek, $officeOpenDays)))) {
            $insertData += 1;

            $shift = EmployeeShiftSchedule::where('user_id', $userId)->where('date', $date->format('Y-m-d'))->first() ?? new EmployeeShiftSchedule();
            $shift->user_id = $userId;
            $shift->date = $date->copy()->format('Y-m-d');
            $shift->employee_shift_id = $request->shift;
            $shift->added_by = user()->id;
            $shift->last_updated_by = user()->id;
            $shift->remarks = $request->remarks;
            $shift->shift_start_time = $date->copy()->toDateString() . ' ' . $shift->shift->office_start_time;

            if (Carbon::parse($shift->shift->office_start_time)->gt(Carbon::parse($shift->shift->office_end_time))) {
                $shift->shift_end_time = $date->copy()->addDay()->toDateString() . ' ' . $shift->shift->office_end_time;
            }
            else {
                $shift->shift_end_time = $date->copy()->toDateString() . ' ' . $shift->shift->office_end_time;
            }

            $shift->saveQuietly();

            if ($request->hasFile('file')) {
                $fileName = Files::uploadLocalOrS3($request->file, 'employee-shift-file/' . $shift->id);
                EmployeeShiftSchedule::where('id', $shift->id)->update(['file' => $fileName]);
            }
        }
    }

}
