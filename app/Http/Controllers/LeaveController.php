<?php

namespace App\Http\Controllers;

use App\DataTables\LeaveDataTable;
use App\Helper\Reply;
use App\Http\Requests\Leaves\ActionLeave;
use App\Http\Requests\Leaves\StoreLeave;
use App\Http\Requests\Leaves\UpdateLeave;
use App\Models\EmployeeDetails;
use App\Models\EmployeeLeaveQuota;
use App\Exports\LeaveExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveSetting;
use App\Models\LeaveType;
use App\Models\Company;
use App\Models\User;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\AttendanceSetting;
use App\Models\EmployeeShift;
use App\Models\Attendance;

class LeaveController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.leaves';
        $this->leaveSetting = LeaveSetting::first();
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leaves', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LeaveDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_leave');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $reportingTo = User::with('employeeDetail')->whereHas('employeeDetail', function ($q) {
            $q->where('reporting_to', user()->id);
        })->get();

        $employee = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
        $this->employees = $reportingTo->merge($employee);

        $this->leaveTypes = LeaveType::all();

        return $dataTable->render('leaves.index', $this->data);
    }

    public function exportAllLeaves(Request $request)
    {
        abort_403(!canDataTableExport());

        $startDate = $request->query('startDate', 'null');
        $endDate = $request->query('endDate', 'null');

        $exportAll = false;
        if($startDate == "null" && $endDate == "null"){
            $exportAll = true;
        }

        $startDate = $startDate !== "null" ? Carbon::createFromFormat(company()->date_format, $startDate) : now();
        $endDate = $endDate !== "null" ? Carbon::createFromFormat(company()->date_format, $endDate) : now();
        $today = now();

        if ($startDate->isSameDay($today) && $endDate->isSameDay($today)) {
            $dateRange = 'Today_' . $today->format('d-m-Y');
        } else {
            $dateRange = $startDate->format('d-m-Y') . '_To_' . $endDate->format('d-m-Y');
        }

        return Excel::download(new LeaveExport($startDate, $endDate, $exportAll), 'Leave_From_' . $dateRange . '.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_leave');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->employees = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));

        $this->currentDate = now()->format('Y-m-d');

        $dateFormat = Company::DATE_FORMATS;
        $this->dateformat = (isset($dateFormat[$this->company->date_format])) ? $dateFormat[$this->company->date_format] : 'DD-MM-YYYY';

        if ($this->addPermission == 'added') {
            $this->defaultAssign = user();
            $this->leaveQuotas = $this->defaultAssign->leaveTypes;
        }
        else if (isset(request()->default_assign)) {
            $this->defaultAssign = User::with('roles')->findOrFail(request()->default_assign);
            $this->leaveQuotas = $this->defaultAssign->leaveTypes;
        }
        else {
            $this->leaveTypes = LeaveType::all();
        }

        if (request()->ajax()) {
            $this->pageTitle = __('modules.leaves.addLeave');
            $html = view('leaves.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leaves.ajax.create';

        return view('leaves.create', $this->data);
    }

    private function checkAttendance(array $dates, $user_id, $leave_half_day = null, $leave_half_day_type = null) {

        foreach ($dates as $date) {

            $userShift = User::findOrFail($user_id)->shifts()->whereDate('date', $date)->first();

            if ($userShift) {
                $halfMarkTime = Carbon::createFromFormat('H:i:s', $userShift->shift->halfday_mark_time, $this->company->timezone);
            } else {
                $attendanceSetting = AttendanceSetting::first();
                $defaultShiftId = $attendanceSetting->default_employee_shift;
                $defaultShift = EmployeeShift::findOrFail($defaultShiftId);


                $halfMarkTime = Carbon::createFromFormat('H:i:s', $defaultShift->halfday_mark_time, $this->company->timezone);
            }

            $halfMarkDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . ' ' . $halfMarkTime->toTimeString(), $this->company->timezone);

            $query = Attendance::whereDate('clock_in_time', $date)
                               ->where('user_id', $user_id);


            if (!is_null($leave_half_day)) {
                $query->where('half_day', $leave_half_day);
            }

            if (!is_null($leave_half_day_type)) {
                $query->where('half_day_type', $leave_half_day_type);
            }

            $attendance = $query->first();

            if ($attendance) {
                return true;
            }

            if (is_null($leave_half_day)) {
                $additionalCheck = Attendance::whereDate('clock_in_time', $date)
                    ->where('user_id', $user_id)
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$additionalCheck) {
                    return false;
                }

                $clockInTime = Carbon::createFromFormat('Y-m-d H:i:s', $additionalCheck->clock_in_time, 'UTC')
                ->setTimezone($this->company->timezone);

                if ($leave_half_day_type == 'first_half') {
                    if($clockInTime->lessThan($halfMarkDateTime))
                    {
                        return true;
                    }
                } else if ($leave_half_day_type == 'second_half') {
                    if($clockInTime->greaterThan($halfMarkDateTime))
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param StoreLeave $request
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreLeave $request)
    {
        $this->addPermission = user()->permission('add_leave');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('leaves.index');
        }

        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $employee = User::withoutGlobalScope(ActiveScope::class)->with('roles')->findOrFail($request->user_id);
        $employeeLeaveQuota = EmployeeLeaveQuota::whereUserId($request->user_id)->whereLeaveTypeId($request->leave_type_id)->first();

        if ($leaveType && !$leaveType->leaveTypeCondition($leaveType, $employee)) {
            return Reply::error(__('messages.leaveTypeNotAllowed'));
        }

        $duration = match ($request->duration) {
            'first_half', 'second_half' => 'half day',
            default => $request->duration,
        };

        $employeeLeaveQuotaRemaining = $employeeLeaveQuota->leaves_remaining;

        $multiDates = [];

        if ($request->duration == 'multiple') {
            $sDate = Carbon::createFromFormat('Y-m-d', $request->multiStartDate);
            $eDate = Carbon::createFromFormat('Y-m-d', $request->multiEndDate);
            $multipleDates = CarbonPeriod::create($sDate, $eDate);

            foreach ($multipleDates as $multipleDate) {
                $multiDates[] = $multipleDate->startOfDay();
            }

            session(['leaves_duration' => 'multiple']);
        }
        else {
            $leaveDate = Carbon::createFromFormat($this->company->date_format, $request->leave_date);
            $multiDates[] = $leaveDate->startOfDay();;
        }

        $leave_half_day_type = null;
        $leave_half_day = null;

        if ($duration == 'half day') {
            $leave_half_day_type = $request->duration;
            $leave_half_day = 'yes';
        }

        // check for the attendance on that day and throw warning if exits
        $this->attendanceAlreadyMarked = $this->checkAttendance($multiDates, $request->user_id, $leave_half_day, $leave_half_day_type);

        if($this->attendanceAlreadyMarked && $request->markLeave == 'no') {
            return Reply::dataOnly(['status' => 'attendanceMarked']);
        }

        $multiDatesFormatted = collect($multiDates)->map(function ($date) {
            return $date->format('Y-m-d');
        });


        $holidays = Holiday::whereIn('date', $multiDatesFormatted)
            ->where(function ($query) use ( $employee) {
                $query->where(function ($subquery) use ( $employee) {
                    $subquery->whereJsonContains('department_id_json', $employee->employeeDetails->department_id)
                        ->orWhereNull('department_id_json');
                });
                $query->where(function ($subquery) use ( $employee) {
                    $subquery->whereJsonContains('designation_id_json', $employee->employeeDetails->designation_id)
                        ->orWhereNull('designation_id_json');
                });
                $query->where(function ($subquery) use ( $employee) {
                    $subquery->whereJsonContains('employment_type_json', $employee->employeeDetails->employment_type)
                        ->orWhereNull('employment_type_json');
                });
            })
        ->get('date');

        $multiDates = collect($multiDates)->filter(function ($date) use ($holidays) {
            return $holidays->where('date', $date)->isEmpty();
        });

        $multiDatesWithoutHolidayFormatted = collect($multiDates)->map(function ($date) {
            return $date->format('Y-m-d');
        });

        $leaveApplied = Leave::whereIn('status', ['approved', 'pending'])
            ->where('user_id', $request->user_id)
            ->whereIn('leave_date', $multiDatesWithoutHolidayFormatted)
            ->get();


        $pendingAppliedLeavesCount = Leave::where('user_id', $request->user_id)
            ->where('status', 'pending')
            ->where('leave_type_id', $request->leave_type_id)
            ->count();

        $halfDayApprovedLeaves = $leaveApplied->where('status', 'approved')->where('duration', 'half day');
        $fullDayApprovedLeaves = $leaveApplied->where('status', 'approved')->where('duration', '!=', 'half day');

        $halfDayLeavesCount = $halfDayApprovedLeaves->count();
        $fullDayLeavesCount = $fullDayApprovedLeaves->count();

        $appliedLeavesCount = $fullDayLeavesCount + ($halfDayLeavesCount * 0.5);

        $totalAllowedLeaves = $employeeLeaveQuotaRemaining + $appliedLeavesCount - $pendingAppliedLeavesCount;

        $applyLeavesCount = ($multiDates->count() * ($duration == 'half day' ? 0.5 : 1));

        if ($totalAllowedLeaves < $applyLeavesCount && $leaveType->over_utilization == 'not_allowed') {
            return Reply::error(__('messages.leaveLimitError'));
        }

        if ($multiDates->count() == 0) {
            return Reply::error(__('messages.noLeaveApplyForSelectedDate'));
        }

        $currentMonthLeaves = Leave::where('leave_type_id', $leaveType->id)
            ->where('user_id', $request->user_id)
            ->whereBetween('leave_date', [$multiDates->first()->copy()->startOfMonth(), $multiDates->first()->copy()->endOfMonth()])
            ->whereIn('status', ['approved', 'pending'])
            ->get();

        $currentMonthLeavesCount = ($currentMonthLeaves->where('duration', 'half day')->count() * 0.5) + $currentMonthLeaves->where('duration', '!=', 'half day')->count();

        $currentMonthAppliedLeavesCount = $currentMonthLeavesCount + $applyLeavesCount;

        if ($leaveType->monthly_limit && $currentMonthAppliedLeavesCount > $leaveType->monthly_limit && $leaveType->over_utilization == 'not_allowed') {
            return Reply::error(__('messages.monthlyLeaveLimitError'));
        }

        $uniqueId = Str::random(16);
        $leaveIds = [];

        DB::beginTransaction();

        foreach ($leaveApplied as $key => $oldLeave) {
            if ($duration == 'half day' && $oldLeave->duration == 'half day' && $oldLeave->half_day_type != $request->duration) {
                continue;
            }

            $oldLeave->status = 'rejected';
            $oldLeave->reject_reason = __('messages.leaveRejectedByNewLeave');
            $oldLeave->save();
        }

        foreach ($multiDates as $key => $leaveDate) {
            $leave = new Leave();
            $leave->user_id = $request->user_id;
            $leave->unique_id = $uniqueId;
            $leave->leave_type_id = $request->leave_type_id;
            $leave->duration = $duration;
            $leave->paid = $leaveType->paid;

            if ($duration == 'half day') {
                $leave->half_day_type = $request->duration;
            }

            $leave->leave_date = $leaveDate->format('Y-m-d');
            $leave->reason = $request->reason;
            $leave->status = ($request->has('status') ? $request->status : 'pending');
            $leave->save();

            $leaveIds[] = $leave->id;

            session()->forget('leaves_duration');
        }

        DB::commit();

        session()->forget('leaves_duration');
        return Reply::successWithData(__('messages.leaveApplySuccess'), ['leaveIds' => $leaveIds, 'redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->leave = Leave::with('approvedBy', 'user')->where(function($q) use($id){
            $q->where('id', $id);
            $q->orWhere('unique_id', $id);
        })->firstOrFail();

        $this->reportingTo = EmployeeDetails::where('reporting_to', user()->id)->first();

        $this->viewPermission = user()->permission('view_leave');
        abort_403(!($this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && user()->id == $this->leave->added_by)
            || ($this->viewPermission == 'owned' && user()->id == $this->leave->user_id)
            || ($this->viewPermission == 'both' && (user()->id == $this->leave->user_id || user()->id == $this->leave->added_by)) || ($this->reportingTo)
        ));

        $this->pageTitle = $this->leave->user->name;
        $this->reportingPermission = LeaveSetting::value('manager_permission');

        if (request()->ajax()) {
            $html = view('leaves.ajax.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        if ($this->leave->duration == 'multiple' && !is_null($this->leave->unique_id) && (request()->type != 'single' || !request()->has('type'))) {
            $this->multipleLeaves = Leave::with('type', 'user')->where('unique_id', $id)->orderByDesc('leave_date')->get();
            $this->viewType = 'multiple';
            $this->pendingCountLeave = $this->multipleLeaves->where('status', 'pending')->count();

            $this->view = 'leaves.ajax.multiple-leaves';
        }
        else {
            $this->view = 'leaves.ajax.show';
        }

        return view('leaves.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->leave = Leave::with('files')->findOrFail($id);
        $this->editPermission = user()->permission('edit_leave');
        abort_403(!(
            ($this->editPermission == 'all'
                || ($this->editPermission == 'added' && $this->leave->added_by == user()->id)
                || ($this->editPermission == 'owned' && $this->leave->user_id == user()->id)
                || ($this->editPermission == 'both' && ($this->leave->user_id == user()->id || $this->leave->added_by == user()->id))
            )
            && ($this->leave->status == 'pending')));


        $this->pageTitle = $this->leave->user->name;

        if ($this->editPermission == 'added') {
            $this->defaultAssign = user();
            $this->leaveUser = $this->defaultAssign;
        }
        else if (isset(request()->default_assign)) {
            $this->defaultAssign = User::withoutGlobalScope(ActiveScope::class)->with('leaveTypes')->findOrFail(request()->default_assign);
            $this->leaveUser = $this->defaultAssign;
        }
        else {
            $this->leaveUser = User::withoutGlobalScope(ActiveScope::class)->with('leaveTypes')->findOrFail($this->leave->user_id);
        }

        $this->employees = User::allEmployees(null, false);
        $assignedEmployee = $this->employees->where('id', $this->leave->user_id)->first();

        if ($assignedEmployee->status == 'active'){
            $this->employees = User::allEmployees(null, true);
        }

        $this->leaveQuotas = $this->leaveUser->leaveTypes->where('leaves_remaining', '>', 0);

        if (request()->ajax()) {
            $html = view('leaves.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leaves.ajax.edit';

        return view('leaves.create', $this->data);
    }

    /**
     * @param UpdateLeave $request
     * @param int $id
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateLeave $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $this->editPermission = user()->permission('edit_leave');

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $leave->added_by == user()->id)
            || ($this->editPermission == 'owned' && $leave->user_id == user()->id)
            || ($this->editPermission == 'both' && ($leave->user_id == user()->id || $leave->added_by == user()->id))
        ));

        $newLeaveDate = Carbon::createFromFormat($this->company->date_format, $request->leave_date);
        $existingLeaveDate = Carbon::parse($leave->leave_date);
        $leave_half_day = null;


        // while edit if leave date is changed then check again for attendance
        if ($newLeaveDate->ne($existingLeaveDate)) {

            $dates = [$newLeaveDate];
            if ($leave->duration == 'half day') {
                $leave_half_day = 'yes';
            }


            if ($this->checkAttendance($dates, $leave->user_id, $leave_half_day, $leave->half_day_type)  && $request->markLeave == 'no') {

                return Reply::dataOnly(['status' => 'attendanceMarked']);
            }
        }

        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $employee = User::withoutGlobalScope(ActiveScope::class)->with('roles')->findOrFail($request->user_id);

        if ($leaveType && !$leaveType->leaveTypeCondition($leaveType, $employee)) {
            return Reply::error(__('messages.leaveTypeNotAllowed'));
        }

        $leaveDate = Carbon::createFromFormat($this->company->date_format, $request->leave_date);

        $applyLeavesCount = ($leave->duration == 'half day' ? 0.5 : 1);

        $holiday = Holiday::where('date', $leaveDate->format('Y-m-d'))->first();

        if ($holiday) {
            return Reply::error(__('messages.holidayLeaveApplyError'));
        }

        $leaveApplied = Leave::whereIn('status', ['approved', 'pending'])
            ->where('user_id', $request->user_id)
            ->where('leave_date', $leaveDate->format('Y-m-d'))
            ->when($leave->duration == 'half day', function ($q) use ($request) {
                $q->where('duration', 'half day');
                $q->where('half_day_type', $request->half_day_type);
            })
            ->where('id', '!=', $id)
            ->first();

        if ($leaveApplied) {
            return Reply::error(__('messages.leaveApplyError'));
        }

        $currentMonthLeaves = Leave::where('leave_type_id', $leaveType->id)
            ->where('user_id', $request->user_id)
            ->whereBetween('leave_date', [$leaveDate->copy()->startOfMonth(), $leaveDate->copy()->endOfMonth()])
            ->whereIn('status', ['approved', 'pending'])
            ->get();

        $currentMonthLeavesCount = ($currentMonthLeaves->where('duration', 'half day')->count() * 0.5) + $currentMonthLeaves->where('duration', '!=', 'half day')->count();

        $currentMonthAppliedLeavesCount = $currentMonthLeavesCount + $applyLeavesCount;

        if ($leaveType->monthly_limit && $currentMonthAppliedLeavesCount > $leaveType->monthly_limit) {
            return Reply::error(__('messages.monthlyLeaveLimitError'));
        }


        $employeeLeaveQuota = EmployeeLeaveQuota::whereUserId($request->user_id)->whereLeaveTypeId($request->leave_type_id)->first();

        $employeeLeaveQuotaRemaining = $employeeLeaveQuota->leaves_remaining;

        if ($employeeLeaveQuotaRemaining < $applyLeavesCount) {
            return Reply::error(__('messages.leaveLimitError'));
        }

        $leave->user_id = $request->user_id;
        $leave->leave_type_id = $request->leave_type_id;
        $leave->leave_date = companyToYmd($request->leave_date);
        $leave->reason = $request->reason;

        if ($request->has('reject_reason')) {
            $leave->reject_reason = $request->reject_reason;
        }

        if ($request->has('status')) {
            $leave->status = $request->status;
        }

        $leave->save();

        if ($leave->duration == 'multiple' && $leave->unique_id){
            $route = route('leaves.show', $leave->unique_id).'?tab=multiple-leaves';
        }
        else{
            $route = route('leaves.index');
        }

        return Reply::successWithData(__('messages.leaveAssignSuccess'), ['leaveID' => $leave->id, 'redirectUrl' => $route]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        $uniqueID = $leave->unique_id;

        $this->deletePermission = user()->permission('delete_leave');
        $this->deleteApproveLeavePermission = user()->permission('delete_approve_leaves');

        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $leave->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $leave->user_id == user()->id)
            || ($this->deletePermission == 'both' && ($leave->user_id == user()->id || $leave->added_by == user()->id) || ($this->deleteApproveLeavePermission == 'none'))
        ));

        if(!is_null(request()->uniId) && request()->duration == 'multiple')
        {
            $leaves = Leave::where('unique_id', request()->uniId)->get();

            foreach ($leaves as $item) {
                Leave::destroy($item->id);
            }
        }
        else {
            Leave::destroy($id);
        }

        $totalLeave = $leave->duration == 'multiple' && !is_null($uniqueID) ? Leave::where('unique_id', $uniqueID)->count() : 0;

        if($totalLeave == 0){
            $route = route('leaves.index');
        }
        elseif(request()->type == 'delete-single' && !is_null($uniqueID) && $leave->duration == 'multiple'){
            $route = route('leaves.show', $leave->unique_id);
        }
        else{
            $route = '';
        }

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $route]);
    }

    public function leaveCalendar(Request $request)
    {
        $viewPermission = user()->permission('view_leave');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->pendingLeaves = Leave::where('status', 'pending')->count();
        $this->employees = User::allEmployees();
        $this->leaveTypes = LeaveType::all();
        $this->pageTitle = 'app.menu.calendar';
        $this->reportingPermission = LeaveSetting::value('manager_permission');

        if (request('start') && request('end')) {

            $leaveArray = array();

            $leavesList = Leave::join('users', 'users.id', 'leaves.user_id')
                ->join('leave_types', 'leave_types.id', 'leaves.leave_type_id')
                ->join('employee_details', 'employee_details.user_id', 'users.id')
                ->where('users.status', 'active')
                ->select('leaves.id', 'users.name', 'leaves.leave_date', 'leaves.status', 'leave_types.type_name', 'leave_types.color', 'leaves.leave_date', 'leaves.duration', 'leaves.status');

            if (!is_null($request->startDate)) {
                $startDate = companyToDateString($request->startDate);
                $leavesList->whereRaw('Date(leaves.leave_date) >= ?', [$startDate]);
            }

            if (!is_null($request->endDate)) {
                $endDate = companyToDateString($request->endDate);

                $leavesList->whereRaw('Date(leaves.leave_date) <= ?', [$endDate]);
            }

            if ($request->leaveTypeId != 'all' && $request->leaveTypeId != '') {
                $leavesList->where('leave_types.id', $request->leaveTypeId);
            }

            if ($request->status != 'all' && $request->status != '') {
                $leavesList->where('leaves.status', $request->status);
            }

            if ($request->searchText != '') {
                $leavesList->where('users.name', 'like', '%' . $request->searchText . '%');
            }

            if ($viewPermission == 'owned') {
                $leavesList->where(function ($q) {
                    $q->orWhere('leaves.user_id', '=', user()->id);

                    ($this->reportingPermission != 'cannot-approve') ? $q->orWhere('employee_details.reporting_to', user()->id) : '';
                });
            }

            if ($viewPermission == 'added') {
                $leavesList->where(function ($q) {
                    $q->orWhere('leaves.added_by', '=', user()->id);

                    ($this->reportingPermission != 'cannot-approve') ? $q->orWhere('employee_details.reporting_to', user()->id) : '';
                });
            }

            if ($viewPermission == 'both') {
                $leavesList->where(function ($q) {
                    $q->orwhere('leaves.user_id', '=', user()->id);

                    $q->orWhere('leaves.added_by', '=', user()->id);

                    ($this->reportingPermission != 'cannot-approve') ? $q->orWhere('employee_details.reporting_to', user()->id) : '';
                });
            }

            $leaves = $leavesList->get();

            foreach ($leaves as $key => $leave) {
                /** @phpstan-ignore-next-line */
                $title = $leave->name;

                $leaveArray[] = [
                    'id' => $leave->id,
                    'title' => $title,
                    'start' => $leave->leave_date->format('Y-m-d'),
                    'end' => $leave->leave_date->format('Y-m-d'),
                    /** @phpstan-ignore-next-line */
                    'color' => $leave->color
                ];
            }

            return $leaveArray;
        }

        return view('leaves.calendar.index', $this->data);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-leave-status':
            $result = $this->changeBulkStatus($request);

            if ($result['updated'] > 0 && $result['skipped'] > 0) {
                $message = __('messages.updateSuccessWithSomeSkipped');
            } elseif ($result['updated'] > 0) {
                $message = __('messages.updateSuccess');
            } else {
                $message = __('messages.noUpdateMade');
            }

            return Reply::success($message);
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_leave') != 'all');
        $leaves = Leave::whereIn('id', explode(',', $request->row_ids))->get();

        foreach($leaves as $leave)
        {
            if(!is_null($leave->unique_id) && $leave->duration == 'multiple')
            {
                $leavesWithSameUniqueId = Leave::where('unique_id', $leave->unique_id)->get();
                foreach ($leavesWithSameUniqueId as $singleLeave) {
                    Leave::destroy($singleLeave->id);
                }
            }
            else {
                Leave::destroy($leave->id);
            }
        }
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_leave') != 'all');

        $leaves = Leave::whereIn('id', explode(',', $request->row_ids))->get();

        $updatedCount = 0;
        $skippedCount = 0;
        foreach($leaves as $leave)
        {
            // Check if the leave status is already approved or rejected
            if ($leave->duration != 'multiple' && in_array($leave->status, ['approved', 'rejected'])) {
                $skippedCount++;
                continue;
            }

            if(!is_null($leave->unique_id) && $leave->duration == 'multiple')
            {
                $uniqueLeaves = Leave::where('unique_id', $leave->unique_id)->get();

                foreach($uniqueLeaves as $uniqueLeave)
                {

                    // Ensure the unique leave status is not approved or rejected
                    if (in_array($uniqueLeave->status, ['approved', 'rejected'])) {
                        $skippedCount++;
                        continue;
                    }

                    $uniqueLeave->status = $request->status;
                    $uniqueLeave->save();
                    $updatedCount++;
                }
            }
            else {

                $leave->status = $request->status;
                $leave->save();
                $updatedCount++;
            }
        }

        return [
            'updated' => $updatedCount,
            'skipped' => $skippedCount
        ];

    }

    public function leaveAction(ActionLeave $request)
    {
        $this->reportingTo = EmployeeDetails::where('reporting_to', user()->id)->first();

        abort_403(!($this->reportingTo) && user()->permission('approve_or_reject_leaves') == 'none');

        if($request->type == 'single'){
            $leave = Leave::findOrFail($request->leaveId);
            $this->leaveStore($leave, $request);
        }
        else {
            $leaves = Leave::where('unique_id', $request->leaveId)->where('status', 'pending')->get();
            session(['leaves_notification' => 'multiple']);
            $totalLeaves = $leaves->count();

            if($totalLeaves > 1){
                $firstLeaveDate = $leaves->first() ? $leaves->first()->leave_date->format($this->company->date_format) : null;
                $lastLeaveDate = $leaves->last() ? $leaves->last()->leave_date->format($this->company->date_format) : null;
                $dateRange = $firstLeaveDate ? $firstLeaveDate . ' to ' . $lastLeaveDate : null;
                session(['dateRange' => $dateRange]);
            }

            foreach ($leaves as $index => $leave) {
                if ($index === $totalLeaves - 1) {
                    if (session()->has('leaves_notification')) {
                        session()->forget('leaves_notification');
                    }
                }

                $this->leaveStore($leave, $request);
            }

        }

        if (session()->has('dateRange')) {
            session()->forget('dateRange');
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function leaveStore($leave, $request)
    {
        $leave->status = $request->action;

        if (isset($request->approveReason)) {
            $leave->approve_reason = $request->approveReason;
        }

        if (isset($request->reason)) {
            $leave->reject_reason = $request->reason;
        }

        $leave->approved_by = user()->id;
        $leave->approved_at = now()->toDateTimeString();
        $leave->save();
    }

    public function preApprove(Request $request)
    {
        $this->reportingTo = EmployeeDetails::where('reporting_to', user()->id)->first();
    
        if ($request->has('leaveUId') && $request->leaveUId != null) {
            Leave::where('unique_id', $request->leaveUId)
                ->update(['manager_status_permission' => $request->action]);
        } else {
            Leave::where('id', $request->leaveId)
                ->update(['manager_status_permission' => $request->action]);
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function approveLeave(Request $request)
    {
        $this->reportingTo = EmployeeDetails::where('reporting_to', user()->id)->first();

        abort_403(!($this->reportingTo) && (user()->permission('approve_or_reject_leaves') == 'none'));

        $this->leaveAction = $request->leave_action;
        $this->leaveID = $request->leave_id;
        $this->type = $request->type;

        return view('leaves.approve.index', $this->data);
    }

    public function rejectLeave(Request $request)
    {
        $this->reportingTo = EmployeeDetails::where('reporting_to', user()->id)->first();

        abort_403(!($this->reportingTo) && (user()->permission('approve_or_reject_leaves') == 'none'));

        $this->leaveAction = $request->leave_action;
        $this->leaveID = $request->leave_id;
        $this->type = $request->type;

        return view('leaves.reject.index', $this->data);
    }

    public function personalLeaves()
    {
        $this->pageTitle = __('modules.leaves.myLeaves');

        $this->employee = User::with(['employeeDetail', 'employeeDetail.designation', 'employeeDetail.department', 'leaveTypes', 'leaveTypes.leaveType', 'country', 'employee', 'roles'])
            ->withoutGlobalScope(ActiveScope::class)
            ->withCount('member', 'agents', 'tasks')
            ->findOrFail(user()->id);

        $settings = company();
        $now = Carbon::now();
        $yearStartMonth = $settings->year_starts_from;
        $leaveStartDate = null;
        $leaveEndDate = null;

        if($settings && $settings->leaves_start_from == 'year_start'){

            if ($yearStartMonth > $now->month) {
                // Not completed a year yet
                $leaveStartDate = Carbon::create($now->year, $yearStartMonth, 1)->subYear();
                $leaveEndDate = $leaveStartDate->copy()->addYear()->subDay();
            } else {
                $leaveStartDate = Carbon::create($now->year, $yearStartMonth, 1);
                $leaveEndDate = $leaveStartDate->copy()->addYear()->subDay();
            }

        } elseif ($settings && $settings->leaves_start_from == 'joining_date'){

            $joiningDate = Carbon::parse($this->employee->employeedetails->joining_date->format((now(company()->timezone)->year) . '-m-d'));
            $joinMonth = $joiningDate->month;
            $joinDay = $joiningDate->day;

            if ($joinMonth > $now->month || ($joinMonth == $now->month && $now->day < $joinDay)) {
                // Not completed a year yet
                $leaveStartDate = $joiningDate->copy()->subYear();
                $leaveEndDate = $joiningDate->copy()->subDay();
            } else {
                // Completed a year
                $leaveStartDate = $joiningDate;
                $leaveEndDate = $joiningDate->copy()->addYear()->subDay();
            }

        }

        $this->employeeLeavesQuotas = $this->employee->leaveTypes;

        $hasLeaveQuotas = false;
        $totalLeaves = 0;
        $leaveCounts = [];
        $allowedEmployeeLeavesQuotas = []; // Leave Types Which employee can take according to leave type conditions

        foreach ($this->employeeLeavesQuotas as $key => $leavesQuota) {

            if (
                ($leavesQuota->leaveType->deleted_at == null || $leavesQuota->leaves_used > 0) &&
                $leavesQuota->leaveType && ($leavesQuota->leaveType->leaveTypeCondition($leavesQuota->leaveType, $this->employee))) {

                $hasLeaveQuotas = true;
                $allowedEmployeeLeavesQuotas[] = $leavesQuota;

                // $sum = ($leavesQuota->leaveType->deleted_at == null) ? $leavesQuota->leaves_remaining : 0;
                // $totalLeaves = $totalLeaves + ($leavesQuota?->no_of_leaves ?: 0) - ($leaveCounts[$leavesQuota->leave_type_id] ?: 0);
                $totalLeaves = $totalLeaves + ($leavesQuota?->leaves_remaining ?: 0);
            }
        }

        $this->leaveCounts = $leaveCounts;
        $this->hasLeaveQuotas = $hasLeaveQuotas;
        $this->allowedLeaves = $totalLeaves;
        $this->allowedEmployeeLeavesQuotas = $allowedEmployeeLeavesQuotas;
        $this->view = 'leaves.ajax.personal';

        return view('leaves.create', $this->data);
    }

    public function getDate(Request $request)
    {
        if ($request->date != null) {
            $date = companyToDateString($request->date);
            $users = Leave::where('leave_date', $date)->where('status', 'approved')->count();
        }
        else{
            $users = '';
        }

        return Reply::dataOnly(['status' => 'success', 'users' => $users]);
    }

    public function viewRelatedLeave(Request $request)
    {
        $this->editLeavePermission = user()->permission('edit_leave');
        $this->deleteLeavePermission = user()->permission('delete_leave');
        $this->approveRejectPermission = user()->permission('approve_or_reject_leaves');
        $this->deleteApproveLeavePermission = user()->permission('delete_approve_leaves');
        $this->multipleLeaves = Leave::with('type', 'user')->where('unique_id', $request->uniqueId)->orderByDesc('leave_date')->get();
        $this->pendingCountLeave = $this->multipleLeaves->where('status', 'pending')->count();

        $this->viewType = 'model';
        return view('leaves.view-multiple-related-leave', $this->data);
    }

    public function leaveTypeRole($id)
    {
        $roles = User::with('roles')->findOrFail($id);
        $userRole = [];
        $userRoles = $roles->roles->count() > 1 ? $roles->roles->where('name', '!=', 'employee') : $roles->roles;

        foreach($userRoles as $role){
            $userRole[] = $role->id;
        }

        $this->userRole = $userRole;
    }

}
