<?php

namespace App\DataTables;

use App\Models\EmployeeShiftChangeRequest;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ShiftChangeRequestDataTable extends BaseDataTable
{

    /**
     * @param mixed $query
     * @return \Yajra\DataTables\DataTableAbstract|\Yajra\DataTables\EloquentDataTable
     */
    public function dataTable($query)
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="javascript:;" class="dropdown-item approve-request" data-request-id="' . $row->id . '"><i class="fa fa-check mr-2"></i>' . __('app.approve') . '</a>';

                $action .= '<a href="javascript:;" class="dropdown-item decline-request" data-request-id="' . $row->id . '"><i class="fa fa-times mr-2"></i>' . __('app.decline') . '</a>';

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->addColumn('employee_name', function ($row) {
                return $row->shiftSchedule->user->name;
            })
            ->editColumn('name', function ($row) {
                return view('components.employee', [
                    'user' => $row->shiftSchedule->user
                ]);
            })
            ->editColumn('shift_name', function ($row) {
                return $row->shiftSchedule->shift->shift_name . ' ' . __('app.to') . ' ' . $row->shift->shift_name;
            })
            ->editColumn('shift', function ($row) {
                return '<span class="badge badge-info" style="background-color: ' . $row->shiftSchedule->shift->color . '">' . $row->shiftSchedule->shift->shift_name . '</span> ' . __('app.to') . ' <span class="badge badge-info" style="background-color: ' . $row->shift->color . '">' . $row->shift->shift_name . '</span>';
            })
            ->editColumn('date', function ($row) {
                return $row->shiftSchedule->date->translatedFormat(company()->date_format);
            })
            ->editColumn('status', function ($row) {
                return $row->status;
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'name', 'check', 'shift']);
    }

    /**
     * @param EmployeeShiftChangeRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EmployeeShiftChangeRequest $model)
    {
        $request = $this->request();

        $employee = $request->employee;
        $shift = $request->shift_id;

        $model = $model->with('shift', 'shiftSchedule', 'shiftSchedule.shift', 'shiftSchedule.user');
        $model = $model->join('employee_shift_schedules', 'employee_shift_schedules.id', '=', 'employee_shift_change_requests.shift_schedule_id');
        $model = $model->join('users', 'users.id', '=', 'employee_shift_schedules.user_id');
        $model = $model->join('employee_shifts', 'employee_shifts.id', '=', 'employee_shift_schedules.employee_shift_id');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);

            if (!is_null($startDate)) {
                $model->where(DB::raw('DATE(employee_shift_change_requests.`created_at`)'), '>=', $startDate);
            }
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);

            if (!is_null($endDate)) {
                $model->where(function ($query) use ($endDate) {
                    $query->where(DB::raw('DATE(employee_shift_change_requests.`created_at`)'), '<=', $endDate);
                });
            }
        }

        if (!is_null($employee) && $employee !== 'all') {
            $model->where('employee_shift_schedules.user_id', $employee);
        }

        if (!is_null($shift) && $shift !== 'all') {
            $model->where('employee_shift_change_requests.employee_shift_id', '=', $shift);
        }

        $model = $model->where('employee_shift_change_requests.status', 'waiting');

        $model = $model->select('employee_shift_change_requests.*');

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('shift-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["shift-table"].buttons().container()
                     .appendTo( "#table-actions")
                 }',
                'fnDrawCallback' => 'function( oSettings ) {
                   //
                   $(".select-picker").selectpicker();
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
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => !showId()],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('#'), 'visible' => showId()],
            __('app.employee') => ['data' => 'name', 'name' => 'users.name', 'exportable' => false, 'title' => __('app.employee')],
            __('app.name') => ['data' => 'employee_name', 'name' => 'name', 'visible' => false, 'title' => __('app.name')],
            __('modules.attendance.shiftName') => ['data' => 'shift_name', 'name' => 'shift_name', 'title' => __('modules.attendance.shift'), 'visible' => false],
            __('modules.attendance.shift') => ['data' => 'shift', 'name' => 'shift_name', 'title' => __('modules.attendance.shift'), 'exportable' => false],
            __('app.date') => ['data' => 'date', 'name' => 'date', 'title' => __('app.date')],
            __('app.reason') => ['data' => 'reason', 'name' => 'reason', 'title' => __('app.reason')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
