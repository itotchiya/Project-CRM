<?php

namespace App\DataTables;

use App\Models\AutomateShift;
use App\Models\ShiftRotation;
use Yajra\DataTables\Html\Column;

class ShiftRotationDataTable extends BaseDataTable
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();

        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view">
                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a class="dropdown-item openRightModal" href="' . route('shift-rotations.edit', [$row->id]) . '">
                        <i class="fa fa-edit mr-2"></i>
                        ' . trans('app.edit') . '
                    </a>';

            $action .= '<a class="dropdown-item delete-shift-rotation" href="javascript:;" data-rotation-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '</a>';

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->editColumn('rotation_name', fn ($row) => $row->rotation_name ? '<span class="badge badge-info f-12 p-1" style="background-color: '.$row->color_code.'">
        '.$row->rotation_name.'</span>' : '--');

        $datatables->addColumn('no_of_emp', function ($row) {
            $rotationEmployees = AutomateShift::where('employee_shift_rotation_id', $row->id)->pluck('user_id');

            if (count($rotationEmployees) > 0) {
                return '<a href="javascript:;" class="text-darkest-grey" id="manageEmployees" data-rotation-id="' . $row->id . '" data-toggle="tooltip" data-original-title="'. trans('app.manageEmployees') .'">' . count($rotationEmployees) . '</a>';
            }
            else {
                return '<span class="text-darkest-grey" data-rotation-id="' . $row->id . '" data-toggle="tooltip" data-original-title="'. trans('app.assignEmployee') .'">' . count($rotationEmployees) . '</span>';
            }
        });

        $datatables->editColumn('override_shift', function ($row) {
            if ($row->override_shift == 'yes') {
                return '<span class="badge badge-primary mr-1">' . __('app.yes') . '</span>';
            }
            else {
                return '<span class="badge badge-secondary mr-1">' . __('app.no') . '</span>';
            }
        });

        $datatables->editColumn('send_mail', function ($row) {
            if ($row->send_mail == 'yes') {
                return '<span class="badge badge-primary mr-1">' . __('app.yes') . '</span>';
            }
            else {
                return '<span class="badge badge-secondary mr-1">' . __('app.no') . '</span>';
            }
        });

        $datatables->editColumn('status', function ($row) {
            $status = '<select class="form-control select-picker change-rotation-status" data-rotation-id="' . $row->id . '">';
            $status .= '<option ';

            if ($row->status == 'active') {
                $status .= 'selected';
            }

            $status .= ' value="active" data-content="<i class=\'fa fa-circle mr-2 text-light-green\'></i> ' . __('app.active') . '">' . __('app.active') . '</option>';
            $status .= '<option ';

            if ($row->status == 'inactive') {
                $status .= 'selected';
            }

            $status .= ' value="inactive" data-content="<i class=\'fa fa-circle mr-2 text-red\'></i> ' . __('app.inactive') . '">' . __('app.inactive') . '</option>';

            $status .= '</select>';

            return $status;
        });
        $datatables->smart(false);
        $datatables->rawColumns(['rotation_name', 'no_of_emp', 'override_shift', 'send_mail', 'status', 'action']);

        return $datatables;
    }

    /**
     * @param ShiftRotation $model
     * @return ShiftRotation|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(ShiftRotation $model)
    {
        return $model->query();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('shift-rotation-table', 2)
            ->parameters(
                [
                    'initComplete' => 'function () {
                        window.LaravelDataTables["shift-rotation-table"].buttons().container()
                            .appendTo( "#table-actions")
                    }',
                    'fnDrawCallback' => 'function( oSettings ) {
                        $("#shift-rotation-table .select-picker").selectpicker();

                        $("body").tooltip({
                            selector: \'[data-toggle="tooltip"]\'
                        })
                    }',
                ]
            );

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $data = [
            __('app.rotationName') => ['data' => 'rotation_name', 'name' => 'rotation_name', 'title' => __('app.rotationName')],
            __('app.noOfEmp') => ['data' => 'no_of_emp', 'name' => 'no_of_emp', 'title' => __('app.noOfEmp'), 'orderable' => false],
            __('app.replacePreAssignedShift') => ['data' => 'override_shift', 'name' => 'override_shift', 'title' => __('app.replacePreAssignedShift')],
            __('app.sendRotationNotification') => ['data' => 'send_mail', 'name' => 'send_mail', 'title' => __('app.sendRotationNotification')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, $action);
    }

}
