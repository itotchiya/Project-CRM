<?php

namespace App\DataTables;

use App\Models\User;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LeaveReportDataTable extends BaseDataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */

    private $viewLeaveReportPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewLeaveReportPermission = user()->permission('view_leave_report');
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
            ->addColumn('approvedLeave', function ($row) {
                return ($row->count_approved_leaves + ($row->count_approved_half_leaves) / 2) == 0 ? '0' : ($row->count_approved_leaves + ($row->count_approved_half_leaves) / 2);
            })
            ->addColumn('pendingLeave', function ($row) {
                return ($row->count_pending_leaves + ($row->count_pending_half_leaves) / 2) == 0 ? '0' : ($row->count_pending_leaves + ($row->count_pending_half_leaves) / 2);
            })
            ->addIndexColumn()
            ->orderColumn('approvedLeave', 'count_approved_leaves $1')
            ->orderColumn('pendingLeave', 'count_pending_leaves $1')
            ->rawColumns(['approve', 'upcoming', 'pending', 'action', 'name']);
    }

    /**
     * @param User $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(User $model)
    {
        $request = $this->request();
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $employeeId = $request->employeeId;

        if ($request->startDate == '') {
            $startDate = now($this->company->timezone)->startOfMonth();
            $endDate = now($this->company->timezone);
        }
        else {
            $startDate = Carbon::createFromFormat($this->company->date_format, $startDate)->toDateString();
            $endDate = Carbon::createFromFormat($this->company->date_format, $endDate)->toDateString();
        }

        $startDt = '';
        $endDt = '';

        if (!is_null($startDate)) {
            $startDt = 'and DATE(leaves.`leave_date`) >= ' . '"' . $startDate . '"';
        }

        if (!is_null($endDate)) {
            $endDt = 'and DATE(leaves.`leave_date`) <= ' . '"' . $endDate . '"';
        }

        $model = $model->with('leaves')->selectRaw(
            'users.*, designations.name as designation_name,
                ( select count("id") from leaves where user_id = users.id and leaves.duration != \'half day\' and leaves.status = \'approved\' ' . $startDt . ' ' . $endDt . ' ) as count_approved_leaves,
                ( select count("id") from leaves where user_id = users.id and leaves.duration = \'half day\' and leaves.status = \'approved\' ' . $startDt . ' ' . $endDt . ' ) as count_approved_half_leaves,
                ( select count("id") from leaves where user_id = users.id and leaves.duration != \'half day\' and leaves.status = \'pending\' ' . $startDt . ' ' . $endDt . ') as count_pending_leaves,
                ( select count("id") from leaves where user_id = users.id and leaves.duration = \'half day\' and leaves.status = \'pending\' ' . $startDt . ' ' . $endDt . ') as count_pending_half_leaves'
        )->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->onlyEmployee();

        if ($employeeId != 0 && $employeeId != 'all') {
            $model->where('users.id', $employeeId);
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

        $model->groupBy('users.id');

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('leave-report-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["leave-report-table"].buttons().container()
                     .appendTo( "#table-actions")
                 }'
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
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.employee') => ['data' => 'name', 'name' => 'users.name', 'exportable' => false, 'title' => __('app.employee')],
            __('app.name') => ['data' => 'employee_name', 'name' => 'users.name', 'visible' => false, 'title' => __('app.name')],
            __('app.approved') => ['data' => 'approvedLeave', 'name' => 'approvedLeave', 'class' => 'text-center', 'title' => __('app.approved')],
            __('app.pending') => ['data' => 'pendingLeave', 'name' => 'pendingLeave', 'class' => 'text-center', 'title' => __('app.pending')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    }

}
