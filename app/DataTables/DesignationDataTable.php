<?php

namespace App\DataTables;

use App\Models\Designation;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class DesignationDataTable extends BaseDataTable
{

    private $editDesignationPermission;
    private $deleteDesignationPermission;
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->editDesignationPermission = user()->permission('edit_designation');
        $this->deleteDesignationPermission = user()->permission('delete_designation');
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
            ->addIndexColumn()
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->editColumn('name', function ($row) {
                $name = '<h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('designations.show', [$row->id]) . '" class="openRightModal">' . $row->name . '</a></h5>';

                return $name;
            })
            ->editColumn('parent_id', function ($row) {
                // get name of parent designation
                $parent = Designation::where('id', $row->parent_id)->first();

                if ($parent) {
                    return $parent->name;
                }

                return '-';

            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">
<a href="' . route('designations.show', [$row->id]) . '" class="taskView text-darkest-grey f-w-500 openRightModal">' . __('app.view') . '</a>
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';


                if ($this->editDesignationPermission == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('designations.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }


                if ($this->deleteDesignationPermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-designation-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['check', 'action', 'name']);
    }

    /**
     * @param Designation $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(Designation $model)
    {
        $request = $this->request();
        $model = $model->select('*');

        if (request()->searchText != '') {
            $model->where('name', 'like', '%' . request()->searchText . '%');
        }

        if ($request->parentId != 'all' && $request->parentId != null) {
            $departments = Designation::with('childs')->where('id', $request->parentId)->get();

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
            $this->arr[] = $item->id;

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
        $dataTable = $this->setBuilder('Designation-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["Designation-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".statusChange").selectpicker();
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
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'exportable' => true, 'title' => __('app.name')],
            __('app.menu.parent_id') => ['data' => 'parent_id', 'name' => 'parent_id', 'exportable' => true, 'title' => __('app.menu.parent_id') . ' ' . __('app.menu.designation')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
