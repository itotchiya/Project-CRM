<?php

namespace App\DataTables;

use App\Models\GlobalSetting;
use App\Models\Proposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ProposalDataTable extends BaseDataTable
{

    private $editProposalPermission;
    private $addInvoicePermission;
    private $deleteProposalPermission;
    private $viewProposalPermission;
    private $leadFilterFalse;

    public function __construct($leadFilterFalse = false)
    {
        parent::__construct();
        $this->editProposalPermission = user()->permission('edit_lead_proposals');
        $this->addInvoicePermission = user()->permission('add_invoices');
        $this->deleteProposalPermission = user()->permission('delete_lead_proposals');
        $this->viewProposalPermission = user()->permission('view_lead_proposals');
        $this->leadFilterFalse = $leadFilterFalse;
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

                $action .= '<a href="' . route('proposals.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($row->send_status) {
                    $action .= '<a target="_blank" class="dropdown-item" href="' . url()->temporarySignedRoute('front.proposal', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $row->hash) . '">
                                    <i class="fa fa-link mr-2"></i>
                                    ' . __('modules.proposal.publicLink') . '
                                </a>';
                }

                $action .= '<a class="dropdown-item" href="' . route('proposals.download', [$row->id]) . '">
                                <i class="fa fa-download mr-2"></i>
                                ' . trans('app.download') . '
                            </a>';

                if (!$row->signature && $this->editProposalPermission == 'all' || ($this->editProposalPermission == 'added' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item" href="' . route('proposals.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($row->status != 'declined') {
                    $action .= '<a class="dropdown-item sendButton" href="javascript:;" data-toggle="tooltip"  data-proposal-id="' . $row->id . '">
                            <i class="fa fa-paper-plane mr-2"></i>
                            ' . trans('app.send') . '
                        </a>';
                }

                if ($row->status != 'declined' && $row->send_status == 0) {
                    $action .= '<a class="dropdown-item sendButton d-flex justify-content-between align-items-center" data-type="mark_as_send" href="javascript:;"  data-proposal-id="' . $row->id . '">
                                    <div><i class="fa fa-check-double mr-2"></i>
                                    ' . trans('app.markSent') . '
                                    </div>
                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-original-title="' . __('messages.markSentInfo') . '"></i>
                                </a>';
                }

                if (($this->addInvoicePermission == 'all' || ($this->addInvoicePermission == 'added' && user()->id == $row->added_by))) {
                    $action .= '<a class="dropdown-item" href="' . route('invoices.create') . '?proposal=' . $row->id . '" ><i class="fa fa-plus mr-2"></i> ' . __('app.create') . ' ' . __('app.invoice') . '</a>';
                }

                if (!$row->signature && $this->deleteProposalPermission == 'all' || ($this->deleteProposalPermission == 'added' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item delete-proposal-table-row" href="javascript:;" data-proposal-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('client_name', function ($row) {
                return '<a href="' . route('deals.show', $row->deal_id) . '" class="text-darkest-grey">' . $row->deal_name . '</a>';
            })
            ->addColumn('proposal_number', function ($row) {
                return '<a href="' . route('proposals.show', $row->id) . '" class="text-darkest-grey">' . $row->proposal_number . '</a>';
            })
            ->addColumn('contact', function ($row) {
                return '<a href="' . route('lead-contact.show', $row->leadId) . '" class="text-darkest-grey">' . ucwords($row->salutation . ' ') . $row->contact_name . '</a>';
            })
            ->editColumn('status', function ($row) {
                $status = '';

                if ($row->status == 'waiting') {
                    $status = ' <i class="fa fa-circle mr-1 text-yellow f-10"></i>' . __('modules.proposal.' . $row->status);
                }

                if ($row->status == 'declined') {
                    $status = ' <i class="fa fa-circle mr-1 text-red f-10"></i>' . __('modules.proposal.' . $row->status);
                }

                if ($row->status == 'accepted') {
                    $status = ' <i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('modules.proposal.' . $row->status);
                }

                if (!$row->send_status) {
                    $status .= ' <span class="badge badge-secondary">' . __('modules.invoices.notSent') . '</span>';
                }

                return $status;
            })
            ->editColumn('total', function ($row) {
                return currency_format($row->total, $row->currencyId);
            })
            ->editColumn(
                'valid_till',
                function ($row) {
                    return Carbon::parse($row->valid_till)->translatedFormat($this->company->date_format);
                }
            )
            ->editColumn(
                'created_at',
                function ($row) {
                    return Carbon::parse($row->created_at)->translatedFormat($this->company->date_format);
                }
            )
            ->rawColumns(['name', 'action', 'contact', 'status', 'client_name', 'proposal_number'])
            ->removeColumn('currency_symbol');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $request = $this->request();
        $model = Proposal::select([
            'proposals.id',
            'proposals.hash',
            'deals.name as deal_name',
            'proposals.send_status',
            'leads.client_id',
            'leads.id as leadId',
            'deals.id as deal_id',
            'total',
            'valid_till',
            'proposals.status',
            'currencies.currency_symbol',
            'currencies.id as currencyId',
            'leads.company_name',
            'proposals.added_by',
            'proposals.created_at',
            'leads.client_name as contact_name',
            'leads.salutation',
            'proposals.proposal_number',
        ])
            ->with('signature')
            ->join('currencies', 'currencies.id', '=', 'proposals.currency_id')
            ->join('deals', 'deals.id', '=', 'proposals.deal_id')
            ->leftJoin('leads', 'leads.id', '=', 'deals.lead_id');


        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $model = $model->where(DB::raw('DATE(proposals.`created_at`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $model = $model->where(DB::raw('DATE(proposals.`created_at`)'), '<=', $endDate);
        }


        // disable this filter when proposals are retrived from the deal tab
        if (!$this->leadFilterFalse && $request->leadId !== null && $request->leadId != 'null' && $request->leadId != '' && $request->leadId != 'all') {
            $model = $model->where('deals.lead_id', $request->leadId);
        }

        // use the leadid here when accessing the proposals from the deal tab
        if ($this->leadFilterFalse && $request->leadId !== null && $request->leadId != 'null' && $request->leadId != '' && $request->leadId != 'all') {
            $model = $model->where('proposals.deal_id', $request->leadId);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $model = $model->where('proposals.status', '=', $request->status);
        }

        if ($this->viewProposalPermission == 'added') {
            $model = $model->where('proposals.added_by', user()->id);
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('leads.client_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('deals.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('proposals.id', 'like', '%' . request('searchText') . '%')
                    ->orWhere('total', 'like', '%' . request('searchText') . '%')
                    ->orWhere(function ($query) {
                        $query->where('proposals.status', 'like', '%' . request('searchText') . '%');
                    });
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
        $dataTable = $this->setBuilder('invoices-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["invoices-table"].buttons().container()
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
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => false],
            __('modules.lead.proposal') => ['data' => 'proposal_number', 'name' => 'proposal_number', 'title' => __('modules.lead.proposal')],
            __('app.deal') => ['data' => 'client_name', 'name' => 'client_name', 'title' => __('app.deal')],
            __('modules.leadContact.contactName') => ['data' => 'contact', 'name' => 'contact', 'title' => __('modules.leadContact.contactName')],
            __('modules.invoices.total') => ['data' => 'total', 'name' => 'total', 'title' => __('modules.invoices.total')],
            __('app.date') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.date')],
            __('modules.estimates.validTill') => ['data' => 'valid_till', 'name' => 'valid_till', 'title' => __('modules.estimates.validTill')],
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
