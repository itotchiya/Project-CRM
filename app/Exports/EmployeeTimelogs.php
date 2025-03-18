<?php

namespace App\Exports;

use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\AttendanceSetting;
use App\Models\EmployeeShiftSchedule;
use App\Models\EmployeeShift;

/**
 * App\Exports\EmployeeTimelogs
 *
 * @property-read EmployeeTimelogs $user
 * @property-read EmployeeTimelogs $modules
 * @property-read EmployeeTimelogs $viewTimeLogPermission
 * @mixin Eloquent
 */
class EmployeeTimelogs extends AccountBaseController implements FromView, ShouldAutoSize, WithStyles, WithEvents
{

    private $viewTimelogPermission;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('timelogs', $this->user->modules));

            return $next($request);
        });
    }

    public function view(): View
    {
        $this->startDate = $startDate = Carbon::createFromFormat(company()->date_format, urldecode(request()->startDate))->toDateString();
        /** @phpstan-ignore-line */
        $this->endDate = $endDate = Carbon::createFromFormat(company()->date_format, urldecode(request()->endDate))->toDateString();
        /** @phpstan-ignore-line */
        $employee = request()->employee;
        $projectId = request()->projectID;
        $this->viewTimelogPermission = user()->permission('view_timelogs');

        $this->employees = User::join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->leftJoin('project_time_logs', 'project_time_logs.user_id', '=', 'users.id');
        $where = '';

        if ($projectId != 'all') {
            $where .= ' and project_time_logs.project_id="' . $projectId . '" ';
        }

        $this->employees = $this->employees->select(
            'users.name',
            'users.id',
            DB::raw(
                "(SELECT SUM(project_time_logs.total_minutes) FROM project_time_logs WHERE project_time_logs.user_id = users.id and DATE(project_time_logs.start_time) >= '" . $startDate . "' and DATE(project_time_logs.start_time) <= '" . $endDate . "' '" . $where . "' GROUP BY project_time_logs.user_id) as total_minutes"
            ),
            DB::raw(
                "(SELECT Count(leaves.id) FROM leaves WHERE leaves.user_id = users.id and leaves.status = 'approved' and DATE(leaves.leave_date) >= '" . $startDate . "' and DATE(leaves.leave_date) <= '" . $endDate . "' GROUP BY leaves.user_id) as total_leaves"
            )
        );

        if (!is_null($employee) && $employee !== 'all') {
            $this->employees = $this->employees->where('project_time_logs.user_id', $employee);
        }

        if (!is_null($projectId) && $projectId !== 'all') {
            $this->employees = $this->employees->where('project_time_logs.project_id', '=', $projectId);
        }

        if ($this->viewTimelogPermission == 'owned') {
            $this->employees = $this->employees->where('project_time_logs.user_id', user()->id);
        }

        if ($this->viewTimelogPermission == 'added') {
            $this->employees = $this->employees->where('project_time_logs.added_by', user()->id);
        }

        if ($this->viewTimelogPermission == 'both') {
            $this->employees = $this->employees->where(function ($q) {
                $q->where('project_time_logs.added_by', user()->id)
                    ->orWhere('project_time_logs.user_id', '=', user()->id);
            });
        }

        $this->employees = $this->employees->with('employeeDetails')->groupBy('project_time_logs.user_id')
            ->orderBy('users.name')
            ->get();

        foreach ($this->employees as $employee) {
            $employee->total_days = $this->countTotalDays($startDate, $endDate);
            $employee->holidays = $this->countHolidays($employee, $startDate, $endDate);
            $employee->total_weekends = $this->countWeekends($startDate, $endDate);
            $employee->total_working_days = $employee->total_days - $employee->total_weekends - $employee->holidays->count();
            $employee->total_hours = $this->calculateTotalHours($employee, $startDate, $endDate);
        }

        return view('exports.employee_timelogs', $this->data);
    }

    public function calculateTotalHours($user, $startDate, $endDate)
    {
        $totalHours = 0;

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($start->lte($end)) {

            $isHoliday = Holiday::where('date', $start)
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
                })->exists();

            $isOnLeave = DB::table('leaves')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('leave_date', $start)
                ->exists();

            if ($isHoliday || $isOnLeave) {
                $start->addDay();
                continue;
            }

            $assignedShift = EmployeeShiftSchedule::where('user_id', $user->id)
                ->where('date', $start)
                ->first();

            if ($assignedShift) {
                $startTime = Carbon::parse($assignedShift->shift_start_time);
                $endTime = Carbon::parse($assignedShift->shift_end_time);
            } else {
                // No assigned shift, so use the default shift
                $defaultShiftId = AttendanceSetting::first()->default_employee_shift;

                // Get the default shift details
                $defaultShift = EmployeeShift::find($defaultShiftId);
                $startTime = Carbon::parse($defaultShift->office_start_time);
                $endTime = Carbon::parse($defaultShift->office_end_time);
            }
            $hoursForDay = $endTime->diffInHours($startTime);
            $totalHours += $hoursForDay;
            $start->addDay();
        }

        return $totalHours;
    }

    // phpcs:ignore
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $row = 4;

                foreach ($this->employees as $index => $employee) {
                    $holidays = $employee->holidays;
                    if ($holidays->isNotEmpty()) {
                        // Format the holiday note
                        $holidayComments = $holidays->map(function ($holiday) {
                            $occasion = $holiday->occassion;
                            $date = Carbon::parse($holiday->date)->format('d/m/Y');
                            return __('modules.holiday.occasion') . ": {$occasion} (" . __('modules.holiday.date') . " - {$date})";
                        })->toArray();

                        $holidayComment = implode(', ', $holidayComments);
                        $cell = 'F' . ($row + $index);
                        $sheet->getComment($cell)->getText()->createTextRun($holidayComment);
                    }
                }
            }
        ];
    }

    private function countTotalDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        return $start->diffInDays($end) + 1;
    }

    private function countWeekends($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $count = 0;

        while ($start->lte($end)) {
            if ($start->isSaturday() || $start->isSunday()) {
                $count++;
            }
            $start->addDay();
        }

        return $count;
    }

    private function countHolidays($user, $startDate, $endDate)
    {
        $holidays = Holiday::orderBy('date', 'ASC');

        $holidays->where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->orWhere('department_id_json', 'like', '%"' . $user->employeeDetails->department_id . '"%')
                    ->orWhereNull('department_id_json');
            });
            $query->where(function ($q) use ($user) {
                $q->orWhere('designation_id_json', 'like', '%"' . $user->employeeDetails->designation_id . '"%')
                    ->orWhereNull('designation_id_json');
            });
            $query->where(function ($q) use ($user) {
                $q->orWhere('employment_type_json', 'like', '%"' . $user->employeeDetails->employment_type . '"%')
                    ->orWhereNull('employment_type_json');
            });
        });
        $holidays->whereBetween('date', [$startDate, $endDate]);

        return $holidays->get();
    }
}
