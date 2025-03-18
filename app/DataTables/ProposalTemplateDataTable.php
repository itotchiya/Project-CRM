<?php

namespace App\DataTables;

use App\Models\ProposalTemplate;
use Carbon\Carbon;
use Yajra\DataTables\Html\Column;

class ProposalTemplateDataTable extends BaseDataTable
{

    private $viewProposalPermission;
    private $addProposalPermission;
    private $editProposalsPermission;
    private $deleteProposalPermission;
    private $manageProposalTemplate;

    public function __construct()
    {
        parent::__construct();

        $this->viewProposalPermission = user()->permission('view_lead_proposals');
        $this->addProposalPermission = user()->permission('add_lead_proposals');
        $this->editProposalsPermission = user()->permission('edit_lead_proposals');
        $this->deleteProposalPermission = user()->permission('delete_lead_proposals');
        $this->manageProposalTemplate = user()->permission('manage_proposal_template');
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
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">
                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= ' <a href="' . route('proposal-template.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->addProposalPermission == 'all' || $this->addProposalPermission == 'added') {
                    $action .= '<a class="dropdown-item" href="' . route('proposals.create') . '?template=' . $row->id . '">
                        <i class="fa fa-plus mr-2"></i>
                        ' . trans('app.create') . ' ' . trans('app.menu.proposal') . '
                    </a>';
                }

                if ($this->manageProposalTemplate == 'all' || $this->manageProposalTemplate == 'added') {
                    $action .= '<a class="dropdown-item" href="' . route('proposal-template.edit', [$row->id]) . '">
                            <i class="fa fa-edit mr-2"></i>
                            ' . trans('app.edit') . '
                        </a>';
                }

                if ($this->manageProposalTemplate == 'all' || $this->manageProposalTemplate == 'added') {
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
            ->addColumn('name', function ($row) {
                return $row->name;
            })
            ->editColumn('total', function ($row) {
                return currency_format($row->total, $row->currencyId);
            })
            ->editColumn(
                'created_at',
                function ($row) {
                    return Carbon::parse($row->created_at)->translatedFormat($this->company->date_format);
                }
            )
            ->rawColumns(['name', 'action', 'client_name'])
            ->removeColumn('currency_symbol');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(ProposalTemplate $model)
    {
        $request = $this->request();
        $model = $model->select('proposal_templates.id', 'proposal_templates.name', 'proposal_templates.hash', 'total', 'currencies.currency_symbol', 'currencies.id as currencyId', 'proposal_templates.added_by', 'proposal_templates.created_at')
            ->join('currencies', 'currencies.id', '=', 'proposal_templates.currency_id');

        if ($this->manageProposalTemplate == 'added') {
            $model->where(function ($query) {
                return $query->where('proposal_templates.added_by', user()->id);
            });
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('proposal_templates.name', 'like', '%' . request('searchText') . '%');
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
        $dataTable = $this->setBuilder('proposal-template-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["proposal-template-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
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
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id')],
            __('modules.proposal.name') => ['data' => 'name', 'name' => 'name', 'title' => __('modules.proposal.name')],
            __('modules.invoices.total') => ['data' => 'total', 'name' => 'total', 'title' => __('modules.invoices.total')],
            __('app.date') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.date')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
