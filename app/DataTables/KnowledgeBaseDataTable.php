<?php

namespace App\DataTables;

use App\Models\KnowledgeBase;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;

class KnowledgeBaseDataTable extends BaseDataTable
{

    private $editKnowledgebasePermission;
    private $deleteKnowledgebasePermission;

    public function __construct()
    {
        parent::__construct();
        $this->editKnowledgebasePermission = user()->permission('edit_knowledgebase');
        $this->deleteKnowledgebasePermission = user()->permission('delete_knowledgebase');
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

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('knowledgebase.show', $row->id) . '" class="dropdown-item openRightModal" ><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editKnowledgebasePermission == 'all' || ($this->editKnowledgebasePermission == 'added' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('knowledgebase.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteKnowledgebasePermission == 'all' || ($this->deleteKnowledgebasePermission == 'added' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="' . $row->id . '">
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
                'heading',
                function ($row) {
                    $heading = ' <a href="' . route('knowledgebase.show', $row->id) . '" class="openRightModal text-darkest-grey" >' . $row->heading . '</a>';

                    return $heading;
                }
            )
            ->editColumn(
                'created_at',
                function ($row) {
                    return $row->created_at->translatedFormat($this->company->date_format);
                }
            )
            ->editColumn(
                'to',
                function ($row) {
                    return $row->to;
                }
            )
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'heading']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\KnowledgeBase $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(KnowledgeBase $model)
    {
        $request = $this->request();
        $model = $model->select('*');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $model = $model->where(DB::raw('DATE(knowledge_bases.`created_at`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $model = $model->where(DB::raw('DATE(knowledge_bases.`created_at`)'), '<=', $endDate);
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('knowledge_bases.heading', 'like', '%' . request('searchText') . '%');
            });
        }

        if (!in_array('admin', user_roles()) && !in_array('client', user_roles())) {
            $model = $model->where('to', 'employee');
        }

        if (in_array('client', user_roles())) {
            $model = $model->where('to', 'client');
        }

        if (user()->permission('view_knowledgebase') == 'added') {
            $model->where('added_by', user()->id);
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
        $dataTable = $this->setBuilder('knowledgebase-table', 3)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["knowledgebase-table"].buttons().container()
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
                'visible' => true,
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('modules.knowledgeBase.knowledge') => ['data' => 'heading', 'name' => 'heading', 'title' => __('modules.knowledgeBase.knowledge')],
            __('app.date') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.date')],
            __('app.to') => ['data' => 'to', 'name' => 'to', 'title' => __('app.to')],
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
