<?php

namespace App\DataTables;

use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Yajra\DataTables\Html\Column;

class LeaveQuotaReportDataTable extends BaseDataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */

    private $viewLeaveReportPermission;
    private $thisMonthStartDate;

    public function __construct()
    {
        parent::__construct();
        $this->viewLeaveReportPermission = user()->permission('view_leave_report');
        $this->thisMonthStartDate = now()->startOfMonth();
    }

    public function dataTable($query)
    {

        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">
                    <a href="javascript:;" data-user-id="' . $row->id . '" class="taskView view-leaves border-right-0">' . __('app.view') . '</a>
                </div>';

                return $action;
            })
            ->addColumn('employee_name', function ($row) {
                return $row->name;
            })
            ->addColumn('name', function ($row) {
                return view('components.employee', [
                    'user' => $row
                ]);
            })
            ->addColumn('totalLeave', function ($row) {
                $count = $this->getAllowedLeavesQuota($row)->sum('no_of_leaves') ?: 0;
                return number_format((float)$count, 2, '.', '');
            })
            ->addColumn('remainingLeave', function ($row) {
                $count = $this->getAllowedLeavesQuota($row)->sum('leaves_remaining') ?: 0;
                return number_format((float)$count, 2, '.', '');
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'name']);
    }

    /**
     * @param User $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(User $model)
    {
        $request = $this->request();
        $forMontDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfDay();

        $employeeId = $request->employeeId;

        $model = $model->onlyEmployee()
            ->with('employeeDetail')
            ->when(!$this->thisMonthStartDate->eq($forMontDate), function($query) use($forMontDate) {
                $query->with([
                    'leaveQuotaHistory' => function($query) use($forMontDate) {
                        $query->where('for_month', $forMontDate);
                    }
                ])->whereHas('leaveQuotaHistory', function($query) use($forMontDate) {
                    $query->where('for_month', $forMontDate);
                });
            })
            ->when($this->thisMonthStartDate->eq($forMontDate), function($query) {
                $query->with('leaveTypes');
            });

        if ($employeeId != 0 && $employeeId != 'all') {
            $model->where('id', $employeeId);
        }

        if(in_array('employee', user_roles()) && $this->viewLeaveReportPermission == 'owned')
        {
            $model->whereHas('employeeDetail', function($query){
                $query->where('id', user()->id);
            });

        }

        if(in_array('employee', user_roles()) && $this->viewLeaveReportPermission == 'both')
        {
            $model->whereHas('employeeDetail', function($query){
                $query->where('added_by', user()->id)->orWhere('id', user()->id);
            });
        }

        if(in_array('employee', user_roles()) && $this->viewLeaveReportPermission == 'added')
        {
            $model->whereHas('employeeDetail', function($query){
                $query->where('added_by', user()->id);
            });
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
        $dataTable = $this->setBuilder('leave-quota-report-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["leave-quota-report-table"].buttons().container()
                     .appendTo( "#table-actions")
                 }'
            ]);

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
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.employee') => ['data' => 'name', 'name' => 'users.name', 'exportable' => false, 'title' => __('app.employee')],
            __('app.name') => ['data' => 'employee_name', 'name' => 'users.name', 'visible' => false, 'title' => __('app.name')],
            __('modules.leaves.noOfLeaves') => ['data' => 'totalLeave', 'name' => 'totalLeave', 'class' => 'text-center', 'title' => __('app.totalLeave')],
            __('modules.leaves.remainingLeaves') => ['data' => 'remainingLeave', 'name' => 'remainingLeave', 'class' => 'text-center', 'title' => __('modules.leaves.remainingLeaves')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    }

    protected function getAllowedLeavesQuota($row, $param = null)
    {
        $request = $this->request();
        $settings = company();
        $now = Carbon::now();
        $total = 0;
        $overUtilizedLeaves = 0;
        $yearStartMonth = $settings->year_starts_from;
        $leaveStartDate = null;
        $leaveEndDate = null;
        $forMontDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfDay();
        $endofmonth = $forMontDate->copy()->endOfMonth();

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

            $joiningDate = Carbon::parse($row->employeedetails->joining_date->format((now(company()->timezone)->year) . '-m-d'));
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

        // -------------------- month not equal
        if (!$this->thisMonthStartDate->eq($forMontDate)) {
            return $row->leaveQuotaHistory->unique('leave_type_id');
        }
 
        $leaveQuotas = $row->leaveTypes;
        $allowedLeavesQuota = collect([]);

        foreach ($leaveQuotas as $leaveQuota) {

            if($param == 'remain'){
                $count = Leave::where('leave_type_id', $leaveQuota->leave_type_id)
                    ->where('user_id', $row->id)
                    ->where('status', 'approved')
                    ->where(function ($query) use ($leaveStartDate, $leaveEndDate) {
                        $query->where('leave_date', '<=', $leaveEndDate)
                            ->where('leave_date', '>=', $leaveStartDate);
                    })->get()
                    ->sum(function($leave) {
                        return $leave->half_day_type ? 0.5 : 1;
                    });
                $total += $count;

                $overUtilizedLeaves += $leaveQuota->overutilised_leaves;

                info([$row->id . ' - ' . $total . ' '. $leaveQuota->leave_type_id . '**' . $overUtilizedLeaves]);
            }

            if ($leaveQuota->leaveType && ($leaveQuota->leaveType->leaveTypeCondition($leaveQuota->leaveType, $row)))
            {
                $allowedLeavesQuota->push($leaveQuota);
            }
        }

        if($param == 'remain' && $this->thisMonthStartDate->eq($forMontDate)){
            $remain = (($allowedLeavesQuota->sum('no_of_leaves') ?: '0') - $total + $overUtilizedLeaves);
            return number_format((float)$remain, 2, '.', '');

        }else{
            return $allowedLeavesQuota;
        }
    }

}
