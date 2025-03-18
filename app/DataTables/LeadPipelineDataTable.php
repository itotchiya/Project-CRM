<?php

namespace App\DataTables;

use App\Models\LeadPipeline;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LeadPipelineDataTable extends BaseDataTable
{

    private $editPipelinePermission;
    private $addPipelinePermission;
    private $deletePipelinePermission;
    private $viewPipelinePermission;

    /**
     * @var \App\Models\LeadStatus[]|\Illuminate\Database\Eloquent\Collection
     */
    private $status;

    public function __construct()
    {
        parent::__construct();
        $this->addPipelinePermission = user()->permission('add_lead_pipeline');
        $this->editPipelinePermission = user()->permission('edit_lead_pipeline');
        $this->deletePipelinePermission = user()->permission('delete_lead_pipeline');
        $this->viewPipelinePermission = user()->permission('view_lead_pipeline');
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

            $action .= '<a href="' . route('deal-pipelines.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

            if (
                $this->editLeadPermission == 'all'
                || ($this->editLeadPermission == 'added' && user()->id == $row->added_by) || user()->id == $row->added_by)

            {
                $action .= '<a class="dropdown-item" href="' . route('deal-pipelines.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
            }

            if (
                $this->deleteLeadPermission == 'all'
                || ($this->deleteLeadPermission == 'added' && user()->id == $row->added_by)
                || ($this->deleteLeadPermission == 'owned' && user()->id == $row->added_by)
                || ($this->deleteLeadPermission == 'both' && user()->id == $row->added_by)
            ) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-id="' . $row->id . '">
                        <i class="fa fa-trash mr-2"></i>
                        ' . trans('app.delete') . '
                    </a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->editColumn('name', function ($row) {

            return '
                        <div class="media-bod1y">
                    <h5 class="mb-0 f-13 "><a href="' . route('deal-pipelines.show', [$row->id]) . '">' . $row->name . '</a></h5>


                    </div>
                  ';
        });

        $datatables->editColumn('created_at', function ($row) {
            return $row->created_at->translatedFormat($this->company->date_format);
        });

        $datatables->smart(false);
        $datatables->setRowId(fn($row) => 'row-' . $row->id);
        $datatables->rawColumns(['action', 'name']);

        return $datatables;
    }

    /**
     * @param Lead $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(LeadPipeline $model)
    {
        $leadContact = $model->select('lead_pipelines.*');


        if ($this->viewLeadPermission == 'both') {
            $leadContact = $leadContact->where(function ($query) {
                $query->orWhere('leads.added_by', user()->id);
            });
        }

        if ($this->request()->searchText != '') {
            $leadContact = $leadContact->where('lead_pipelines.name', 'like', '%' . request('searchText') . '%');

        }

        return $leadContact;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('lead-pipeline-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["lead-pipeline-table"].buttons().container()
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

        $data = [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => showId()],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'exportable' => false, 'title' => __('app.name')],
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

