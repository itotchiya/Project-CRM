<?php

namespace App\DataTables;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;

class AttendanceReportDataTable extends BaseDataTable
{

    private $attendanceSettings;
    private $totalWorkingDays;
    private $daysPresent;
    private $holidaysCount;
    private $extraDays;
    private $startTime;
    private $endTime;
    private $notClockedOut;

    public function __construct()
    {
        parent::__construct();
        $this->attendanceSettings = attendance_setting();
    }

    /**
     * @param mixed $query
     * @return \Yajra\DataTables\CollectionDataTable|\Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $request = $this->request();
        $startDate = now($this->company->timezone)->startOfMonth();
        $endDate = now($this->company->timezone);

        $diff = 0;

        if ($request->startDate != '') {
            // if this month filter's end date is not equal to now
            $diff = ($endDate->lt(Carbon::createFromFormat($this->company->date_format, $request->endDate))) ? $endDate->diffInDays(Carbon::createFromFormat($this->company->date_format, $request->endDate)) : 0;
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->startOfDay();
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->endOfDay();
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $this->totalWorkingDays = ($diff < 1) ? $startDate->diffInDays($endDate) + 1 : $startDate->diffInDays($endDate) - $diff;

        // if this month filter's end date is not equal to now
        if ($endDate->gt(now($this->company->timezone))) {
            $holidayDate = Holiday::whereBetween(DB::raw('DATE(holidays.`date`)'), [$startDate->toDateString(), now($this->company->timezone)])->get('date');
        }
        else {
            $holidayDate = Holiday::whereBetween(DB::raw('DATE(holidays.`date`)'), [$startDate->toDateString(), $endDate->toDateString()])->get('date');
        }

        $this->holidaysCount = $holidayDate->count();
        $holidays = [];

        foreach ($holidayDate as $item) {
            array_push($holidays, $item->date);
        }

        $this->daysPresent = 0;
        $this->extraDays = 0;

        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('employee_name', fn($row) => $row->name)
            ->addColumn('name', fn($row) => view('components.employee', ['user' => $row]))
            ->addColumn('present_days', fn($row) => $this->daysPresent = Attendance::countDaysPresentByUser($startDate, $endDate, $row->id) ?: '0')
            ->addColumn('extra_days', fn($row) => $this->extraDays($startDate, $endDate, $row->id, $holidays))
            ->addColumn('absent_days', function ($row) {
                $this->holidaysCount = $this->holidaysCount - $this->extraDays;

                if ($this->holidaysCount > 0) {
                    return (($this->totalWorkingDays - ($this->daysPresent + $this->extraDays + $this->holidaysCount)) <= 0) ? '0' : ($this->totalWorkingDays - ($this->daysPresent + $this->extraDays + $this->holidaysCount));
                }

                return (($this->totalWorkingDays - ($this->daysPresent + $this->extraDays)) <= 0) ? '0' : ($this->totalWorkingDays - ($this->daysPresent + $this->extraDays));
            })
            ->addColumn('hours_clocked', fn($row) => $this->calculateHours($period, $row))
            ->addColumn('late_day_count', fn($row) => Attendance::countDaysLateByUser($startDate, $endDate, $row->id) ?: '0')
            ->addColumn('half_day_count', fn($row) => Attendance::countHalfDaysByUser($startDate, $endDate, $row->id) ?: '0')
            ->orderColumn('present_days', 'user_id $1')
            ->orderColumn('absent_days', 'user_id $1')
            ->orderColumn('extra_days', 'user_id $1')
            ->orderColumn('hours_clocked', 'user_id $1')
            ->orderColumn('late_day_count', 'user_id $1')
            ->orderColumn('half_day_count', 'user_id $1');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function query()
    {
        $request = $this->request();
        $model = User::with('role', 'roles', 'employeeDetail', 'session')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'employee')
            ->select('users.*');

        if ($request->employee != 'all') {
            $model = $model->where('users.id', $request->employee);
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('attendance-report-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["attendance-report-table"].buttons().container()
                     .appendTo( "#table-actions")
                 }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.employee') => ['data' => 'name', 'name' => 'users.name', 'exportable' => false, 'title' => __('app.employee')],
            __('app.name') => ['data' => 'employee_name', 'name' => 'users.name', 'visible' => false, 'title' => __('app.name')],
            __('modules.attendance.present') => ['data' => 'present_days', 'name' => 'present_days', 'title' => __('modules.attendance.present')],
            __('modules.attendance.absent') => ['data' => 'absent_days', 'name' => 'absent_days', 'title' => __('modules.attendance.absent')],
            __('modules.attendance.extraDays') => ['data' => 'extra_days', 'name' => 'extra_days', 'title' => __('modules.attendance.extraDays')],
            __('modules.attendance.hoursClocked') => ['data' => 'hours_clocked', 'name' => 'hours_clocked', 'title' => __('modules.attendance.hoursClocked')],
            __('app.days') . ' ' . __('modules.attendance.late') => ['data' => 'late_day_count', 'name' => 'late_day_count', 'title' => __('app.days') . ' ' . __('modules.attendance.late')],
            __('modules.attendance.halfDay') => ['data' => 'half_day_count', 'name' => 'half_day_count', 'title' => __('modules.attendance.halfDay')],
        ];
    }

    public function calculateHours($period, $user)
    {
        $timeLogInMinutes = 0;

        foreach ($period as $date) {
            $attendanceDate = $date->toDateString();
            $clockins = Attendance::select('clock_in_time', 'clock_out_time')
                ->where(DB::raw('DATE(attendances.clock_in_time)'), $attendanceDate)
                ->where('user_id', $user->id)
                ->orderBy('id')
                ->get();

            foreach ($clockins as $value) {

                $this->startTime = $value->clock_in_time->timezone($this->company->timezone);

                if (!is_null($value->clock_out_time)) {
                    $this->endTime = $value->clock_out_time->timezone($this->company->timezone);
                }
                elseif (($value->clock_in_time->timezone($this->company->timezone)->translatedFormat('Y-m-d') != now()->timezone($this->company->timezone)->translatedFormat('Y-m-d')) && is_null($value->clock_out_time)) {
                    $this->endTime = Carbon::parse($this->startTime->translatedFormat('Y-m-d') . ' ' . $this->attendanceSettings->office_end_time, $this->company->timezone);
                    if ($this->endTime->gt(now()->timezone($this->company->timezone))) {
                        $this->endTime = now()->timezone($this->company->timezone);
                    }
                    $this->notClockedOut = true;
                }
                else {
                    $this->notClockedOut = true;
                    $this->endTime = now()->timezone($this->company->timezone);
                }

                $timeLogInMinutes = $timeLogInMinutes + $this->endTime->diffInMinutes($this->startTime, true);
            }

        }

        info($timeLogInMinutes . ' : ' . $user->id);

        if ($timeLogInMinutes <= 59) {
            return sprintf('%d' . __('app.hrs') . ' %d' . __('app.mins'), 0, $timeLogInMinutes);
        }

        return sprintf('%d' . __('app.hrs') . ' %d' . __('app.mins'), floor($timeLogInMinutes / 60), floor($timeLogInMinutes % 60));

    }

    public function extraDays($startDate, $endDate, $userId, $holidays)
    {
        $extraDays = Attendance::whereBetween(DB::raw('DATE(attendances.`clock_in_time`)'), [$startDate->toDateString(), $endDate->toDateString()])
            ->where('user_id', $userId)
            ->whereIn(DB::raw('DATE(`clock_in_time`)'), $holidays)
            ->select(DB::raw('count(DISTINCT DATE(attendances.clock_in_time) ) as attendance'))
            ->first();

        return $extraDays->attendance;
    }

}
