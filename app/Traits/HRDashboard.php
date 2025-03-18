<?php

namespace App\Traits;

use Exception;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\Leave;
use App\Models\Designation;
use App\Models\DashboardWidget;
use App\Models\EmployeeDetails;
use Google\Service\AnalyticsData\OrderBy;
use Illuminate\Support\Facades\DB;

/**
 *
 */
trait HRDashboard
{

    use CurrencyExchange;

    /**
     *
     * @return void
     */
    public function hrDashboard()
    {
        $this->viewHRDashboard = user()->permission('view_hr_dashboard');
        abort_403($this->viewHRDashboard !== 'all');

        $this->pageTitle = 'app.hrDashboard';
        $this->startDate = (request('startDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('startDate')) : now($this->company->timezone)->startOfMonth();
        $this->endDate = (request('endDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('endDate')) : now($this->company->timezone);
        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();

        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-hr-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(function ($value, $key) {
            return $value->status == '1';
        })->pluck('widget_name')->toArray();

        $this->totalLeavesApproved = Leave::whereBetween(DB::raw('DATE(`leave_date`)'), [$startDate, $endDate])->where('status', 'approved')->count();
        $this->totalEmployee = User::allEmployees(null, true)->count();
        $this->totalNewEmployee = EmployeeDetails::whereBetween(DB::raw('DATE(`joining_date`)'), [$startDate, $endDate])->count();
        $this->totalEmployeeExits = EmployeeDetails::whereBetween(DB::raw('DATE(`last_date`)'), [$startDate, $endDate])->count();

        $attandance = EmployeeDetails::join('users', 'users.id', 'employee_details.user_id')
            ->join('attendances', 'attendances.user_id', 'users.id')
            ->whereBetween(DB::raw('DATE(attendances.`clock_in_time`)'), [$startDate, $endDate])
            ->select(DB::raw('count(users.id) as employeeCount'), DB::raw('DATE(attendances.clock_in_time) as date'))
            ->groupBy('date')
            ->get();

        if ($attandance->count() > 0) {
            try {
                $this->averageAttendance = number_format(((array_sum(array_column($attandance->toArray(), 'employeeCount')) / $attandance->count()) * 100) / $this->totalEmployee, 2) . '%';
            } catch (Exception $e) {
                $this->averageAttendance = '0%';
            }

        }
        else {
            $this->averageAttendance = '0%';
        }

        $this->departmentWiseChart = $this->departmentWiseChart();
        $this->designationWiseChart = $this->designationWiseChart();
        $this->genderWiseChart = $this->genderWiseChart();
        $this->roleWiseChart = $this->roleWiseChart();
        $this->headCountChart = $this->headcountChart();
        $this->joiningVsAttritionChart = $this->joiningVsAttritionChart();

        $this->leavesTaken = User::with('employeeDetail', 'employeeDetail.designation')
            ->join('leaves', 'leaves.user_id', 'users.id')
            ->whereBetween(DB::raw('DATE(leaves.`leave_date`)'), [$startDate, $endDate])
            ->where('leaves.status', 'approved')
            ->select(DB::raw('count(leaves.id) as employeeLeaveCount'), 'users.*')
            ->groupBy('users.id')
            ->orderByDesc('employeeLeaveCount')
            ->get();

        $fromMonthDay = carbon::parse($startDate)->format('m-d');
        $tillMonthDay = carbon::parse($endDate)->format('m-d');

        $this->birthdays = EmployeeDetails::with('user')
            ->select('*', 'date_of_birth', DB::raw('MONTH(date_of_birth) months'))
            ->whereNotNull('date_of_birth')
            ->where(function ($query) use ($fromMonthDay, $tillMonthDay) {
                $query->whereRaw('DATE_FORMAT(`date_of_birth`, "%m-%d") BETWEEN "' . $fromMonthDay . '" AND "' . $tillMonthDay . '"');
            })
            ->orderBy('months')
            ->get();

        $this->lateAttendanceMarks = User::with('employeeDetail', 'employeeDetail.designation')
            ->without(['role', 'clientDetails'])
            ->join('attendances', 'attendances.user_id', 'users.id')
            ->whereBetween(DB::raw('DATE(attendances.`clock_in_time`)'), [$startDate, $endDate])
            ->where('late', 'yes')
            ->select(DB::raw('count(DISTINCT DATE(attendances.clock_in_time) ) as employeeLateCount'), 'users.*')
            ->groupBy('users.id')
            ->orderByDesc('employeeLateCount')
            ->get();

        $this->counts = User::select(
            DB::raw('(select count(distinct(attendances.user_id)) from `attendances` inner join users as atd_user on atd_user.id=attendances.user_id inner join role_user on role_user.user_id=atd_user.id inner join roles on roles.id=role_user.role_id WHERE roles.name = "employee" and attendances.clock_in_time >= "' . today(company()->timezone)->setTimezone('UTC')->toDateTimeString() . '" and atd_user.status = "active" AND attendances.company_id = ' . company()->id . ') as totalTodayAttendance'),
            DB::raw('(select count(users.id) from `users` inner join role_user on role_user.user_id=users.id inner join roles on roles.id=role_user.role_id WHERE roles.name = "employee" and users.status = "active" AND users.company_id = ' . company()->id . ') as totalEmployees')
        )
            ->first();

        $this->view = 'dashboard.ajax.hr';
    }

    public function departmentWiseChart()
    {
        $departments = Team::withCount(['teamMembers' => function ($query) {
            $query->join('users', 'users.id', '=', 'employee_details.user_id');
            $query->where('users.status', '=', 'active');
        }])->get();
        $data['labels'] = $departments->pluck('team_name')->toArray();

        foreach ($data['labels'] as $key => $value) {
            $data['colors'][] = '#' . substr(md5($value), 0, 6);
        }

        $data['values'] = $departments->pluck('team_members_count')->toArray();

        return $data;
    }

    public function designationWiseChart()
    {
        $departments = Designation::withCount(['members' => function ($query) {
            $query->join('users', 'users.id', '=', 'employee_details.user_id');
            $query->where('users.status', '=', 'active');
        }])->get();

        $data['labels'] = $departments->pluck('name')->toArray();

        foreach ($data['labels'] as $key => $value) {
            $data['colors'][] = '#' . substr(md5($value), 0, 6);
        }

        $data['values'] = $departments->pluck('members_count')->toArray();

        return $data;
    }

    public function genderWiseChart()
    {

        $genderWiseEmployee = EmployeeDetails::join('users', 'users.id', 'employee_details.user_id')
            ->select(DB::raw('count(employee_details.id) as totalEmployee'), 'users.gender')
            ->whereNotNull('users.gender')
            ->where('users.status', '=', 'active')
            ->groupBy('users.gender')
            ->orderBy('users.gender', 'ASC')
            ->get();

        $labels = $genderWiseEmployee->pluck('gender')->toArray();

        $data['labels'] = [];

        foreach ($labels as $key => $value) {
            $data['labels'][] = __('app.' . $value);
        }

        $data['values'] = $genderWiseEmployee->pluck('totalEmployee')->toArray();
        $data['colors'] = ['#1d82f5', '#FCBD01', '#D30000'];

        return $data;
    }

    public function roleWiseChart()
    {
        $roleWiseChart = Role::withCount(['users' => function ($query) {
            $query->where('users.status', '=', 'active');
        }])
            ->where('name', '<>', 'client')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($roleWiseChart as $key => $value) {
            if ($value->name == 'admin' || $value->name == 'employee') {
                $data['labels'][] = __('app.' . $value->name);
                $data['colors'][] = '#' . substr(md5($value->name), 0, 6);
            }
            else {
                $data['labels'][] = $value->display_name;
                $data['colors'][] = '#' . substr(md5($value), 0, 6);
            }
        }

        $data['values'] = $roleWiseChart->pluck('users_count')->toArray();

        return $data;
    }

    public function headCountChart()
    {
        $period = now(global_setting()->timezone)->subMonths(11)->monthsUntil(now(global_setting()->timezone));
        $startDate = $period->startDate->startOfMonth();
        /** @phpstan-ignore-line */
        $endDate = $period->endDate->endOfMonth();
        /** @phpstan-ignore-line */

        $months = [];

        foreach ($period as $periodData) {
            $months[$periodData->format('m-Y')] = [
                'y' => $periodData?->translatedFormat('F'),
                'a' => 0
            ];
        }

        $oldEmployee = EmployeeDetails::whereDate('joining_date', '<', $startDate)->count();

        $inActiveOldEmployee = EmployeeDetails::whereHas('user', function ($q) use ($startDate) {
            $q->where('status', '=', 'deactive')
                ->where('last_date', '<', $startDate);
        })->count();

        $oldEmployee = $oldEmployee - $inActiveOldEmployee;

        $joiningDates = EmployeeDetails::whereDate('joining_date', '>=', $startDate)->whereDate('joining_date', '<=', $endDate)
            ->select(DB::raw('count(*) as data'),
                DB::raw("DATE_FORMAT(joining_date, '%m-%Y') date"),
                DB::raw('YEAR(joining_date) year, MONTH(joining_date) month'), )
            ->orderBy('joining_date')
            ->groupby('year', 'month')
            ->get()->keyBy('date');

        $lastDates = EmployeeDetails::whereDate('last_date', '>=', $startDate)->whereDate('last_date', '<=', $endDate)
            ->select('id', DB::raw('count(*) as data'),
                DB::raw("DATE_FORMAT(last_date, '%m-%Y') date"),
                DB::raw('YEAR(last_date) year, MONTH(last_date) month'))
            ->orderBy('last_date')
            ->groupby('year', 'month')
            ->get()
            ->keyBy('date');

        $inActiveEmployee = EmployeeDetails::join('users', 'employee_details.user_id', '=', 'users.id')
            ->whereDate('last_date', '>=', $startDate)
            ->whereDate('last_date', '<=', $endDate)
            ->select(DB::raw('count(*) as data'),
                DB::raw("DATE_FORMAT(last_date, '%m-%Y') date"),
                DB::raw('YEAR(users.inactive_date) year, MONTH(last_date) month'))
            ->orderBy('last_date')
            ->groupby('year', 'month')
            ->get()
            ->keyBy('date');


        $graphData = [];

        foreach ($months as $key => $month) {
            $inActiveCount = isset($inActiveEmployee[$key]) ? $inActiveEmployee[$key]->data : 0;
            $oldEmployee = $oldEmployee + (isset($joiningDates[$key]) ? $joiningDates[$key]->data : 0) - $inActiveCount;
            $oldEmployee = $oldEmployee - (isset($lastDates[$key]) ? $lastDates[$key]->data : 0);

            $graphData[] = [
                'y' => $months[$key]['y'],
                'a' => $oldEmployee
            ];
        }

        $graphData = collect($graphData);

        $data['labels'] = $graphData->pluck('y');
        $data['values'] = $graphData->pluck('a')->toArray();
        $data['colors'] = [$this->appTheme->header_color];
        $data['name'] = __('modules.dashboard.headcount');

        return $data;
    }

    public function joiningVsAttritionChart()
    {
        $period = now()->subMonths(11)->monthsUntil(now());

        $startDate = $period->startDate->startOfMonth();
        /** @phpstan-ignore-line */
        $endDate = $period->endDate->endOfMonth();
        /** @phpstan-ignore-line */


        $months = [];

        foreach ($period as $periodData) {
            $months[$periodData->format('m-Y')] = [
                'y' => $periodData ? $periodData->translatedFormat('F') : null,
                'a' => 0,
                'b' => 0
            ];
        }

        $joiningDates = EmployeeDetails::whereDate('joining_date', '>=', $startDate)->whereDate('joining_date', '<=', $endDate)
            ->select(DB::raw('count(joining_date) as data'),
                DB::raw("DATE_FORMAT(joining_date, '%m-%Y') date"),
                DB::raw('YEAR(joining_date) year, MONTH(joining_date) month'))
            ->orderBy('joining_date')
            ->groupby('year', 'month')
            ->get()->keyBy('date');

        $attritionDates = EmployeeDetails::join('users', 'employee_details.user_id', '=', 'users.id')
            ->where('users.status', '=', 'deactive')
            ->whereDate('last_date', '>=', $startDate)->whereDate('last_date', '<=', $endDate)
            ->select(DB::raw('count(last_date) as data'),
                DB::raw("DATE_FORMAT(last_date, '%m-%Y') date"),
                DB::raw('YEAR(last_date) year, MONTH(last_date) month'))
            ->orderBy('last_date')
            ->groupby('year', 'month')
            ->get()->keyBy('date');

        $graphData = [];

        foreach ($months as $key => $month) {
            $joinings = 0;
            $exit = 0;

            if (isset($joiningDates[$key])) {
                $joinings = $joiningDates[$key]->data;
            }

            if (isset($attritionDates[$key])) {
                $exit = $attritionDates[$key]->data;
            }

            $graphData[] = [
                'y' => $months[$key]['y'],
                'a' => $joinings,
                'b' => $exit
            ];

        }

        $graphData = collect($graphData);

        $data['labels'] = $graphData->pluck('y');
        $data['values'][] = $graphData->pluck('a');
        $data['values'][] = $graphData->pluck('b');
        $data['colors'] = ['#1D82F5', '#d30000'];
        $data['name'][] = __('app.joining');
        $data['name'][] = __('app.attrition');

        return $data;

    }

}
