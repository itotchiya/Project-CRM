<?php

namespace App\DataTables;

use App\Models\Team;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class DepartmentDataTable extends BaseDataTable
{

    private $editDepartmentPermission;
    private $deleteDepartmentPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editDepartmentPermission = user()->permission('edit_department');
        $this->deleteDepartmentPermission = user()->permission('delete_department');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">
<a href="' . route('departments.show', [$row->id]) . '" class="taskView text-darkest-grey f-w-500 openRightModal">' . __('app.view') . '</a>
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';


                if ($this->editDepartmentPermission == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('departments.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteDepartmentPermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-department-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn(
                'name',
                function ($row) {
                    return '<h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('departments.show', [$row->id]) . '" class="openRightModal">' . $row->team_name . '</a></h5>';
                }
            )
            ->editColumn('parent_id', function ($row) {
                // get name of parent department
                $parent = Team::where('id', $row->parent_id)->first();

                if ($parent) {
                    return $parent->team_name;
                }

                return '-';
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'name', 'check']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Team $model
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function query(Team $model)
    {
        $request = $this->request();
        $model = $model->select('*');

        if (request()->searchText != '') {
            $model->where('team_name', 'like', '%' . request()->searchText . '%');
        }

        if ($request->parentId != 'all' && $request->parentId != null) {
            $departments = Team::with('childs')->where('id', $request->parentId)->get();

            foreach ($departments as $department) {
                if ($department->childs) {
                    $this->child($department->childs);
                    array_push($this->arr, $department->id);
                }
            }

            $model->whereIn('id', $this->arr);
        }

        return $model;
    }

    public function child($child)
    {
        foreach ($child as $item) {
            array_push($this->arr, $item->id);

            if ($item->childs) {
                $this->child($item->childs);
            }
        }
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('departments-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["departments-table"].buttons().container()
                    .appendTo("#table-actions")
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
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('client', user_roles())
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.name') => ['data' => 'name', 'name' => 'team_name', 'title' => __('app.name')],
            __('modules.department.parentDepartment') => ['data' => 'parent_id', 'name' => 'parent_id', 'exportable' => true, 'title' => __('modules.department.parentDepartment')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
