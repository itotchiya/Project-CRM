<?php

namespace App\DataTables;

use Carbon\Carbon;
use App\Models\Estimate;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\GlobalSetting;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class EstimatesDataTable extends BaseDataTable
{

    protected $firstEstimate;
    private $addEstimatePermission;
    private $editEstimatePermission;
    private $deleteEstimatePermission;
    private $addInvoicePermission;
    private $viewEstimatePermission;
    private $showRequest;

    public function __construct()
    {
        parent::__construct();
        $this->viewEstimatePermission = user()->permission('view_estimates');
        $this->addEstimatePermission = user()->permission('add_estimates');
        $this->editEstimatePermission = user()->permission('edit_estimates');
        $this->deleteEstimatePermission = user()->permission('delete_estimates');
        $this->addInvoicePermission = user()->permission('add_invoices');
        $this->showRequest = in_array(user()->permission('view_estimate_request'), ['all', 'added', 'owned', 'both']);
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $firstEstimate = $this->firstEstimate;


        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();
        $datatables->addColumn('action', function ($row) use ($firstEstimate) {

            $action = '<div class="task_view">

            <div class="dropdown">
                <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                    id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-options-vertical icons"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('estimates.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

            if ($row->send_status) {
                $action .= '<a class="dropdown-item btn-copy" data-clipboard-text="' . url()->temporarySignedRoute('front.estimate.show', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $row->hash) . '">
                        <i class="fa fa-copy mr-2"></i> ' . __('modules.estimates.copyLink') . ' </a>';

                $action .= '<a class="dropdown-item" href="' . url()->temporarySignedRoute('front.estimate.show', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $row->hash) . '" target="_blank"><i class="fa fa-external-link-alt mr-2"></i>' . trans('modules.estimates.viewLink') . '</a>';
            }

            if ($row->status != 'draft') {
                $action .= '<a class="dropdown-item" href="' . route('estimates.download', [$row->id]) . '"> <i class="fa fa-download mr-2"></i>' . trans('app.download') . '</a>';
            }

            if ($row->status == 'waiting' || $row->status == 'draft') {
                if (
                    $this->editEstimatePermission == 'all'
                    || ($this->editEstimatePermission == 'added' && $row->added_by == user()->id)
                    || ($this->editEstimatePermission == 'owned' && $row->client_id == user()->id)
                    || ($this->editEstimatePermission == 'both' && ($row->client_id == user()->id || $row->added_by == user()->id))
                ) {
                    $action .= '<a class="dropdown-item" href="' . route('estimates.edit', [$row->id]) . '"><i class="fa fa-edit mr-2"></i>' . trans('app.edit') . '</a>';
                }
            }

            if ($row->status != 'canceled' && $row->status != 'accepted' && !in_array('client', user_roles())) {
                $action .= '<a href="javascript:;" data-toggle="tooltip"  data-estimate-id="' . $row->id . '" class="dropdown-item sendButton"><i class="fa fa-paper-plane mr-2"></i> ' . __('app.send') . '</a>';
            }

            if ($firstEstimate->id == $row->id) {
                if (
                    $this->deleteEstimatePermission == 'all'
                    || ($this->deleteEstimatePermission == 'added' && $row->added_by == user()->id)
                    || ($this->deleteEstimatePermission == 'owned' && $row->client_id == user()->id)
                    || ($this->deleteEstimatePermission == 'both' && ($row->client_id == user()->id || $row->added_by == user()->id))
                ) {

                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-estimate-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '</a>';
                }
            }

            if ($row->status == 'waiting' || (is_null($row->estimate_id) && $row->status == 'accepted')) {
                if ($this->addInvoicePermission == 'all' || $this->addInvoicePermission == 'added') {
                    $action .= '<a class="dropdown-item" href="' . route('invoices.create') . '?estimate=' . $row->id . '" ><i class="fa fa-plus mr-2"></i> ' . __('app.create') . ' ' . __('app.invoice') . '</a>';
                }

                if ($this->editEstimatePermission == 'all' || ($this->editEstimatePermission == 'added' && $row->added_by == user()->id)) {
                    $action .= '<a href="javascript:;" class="dropdown-item change-status" data-estimate-id="' . $row->id . '" ><i class="fa fa-times mr-2"></i> ' . __('app.cancelEstimate') . '</a>';
                }
            }

            if ($this->addEstimatePermission == 'all' || $this->addEstimatePermission == 'added') {
                $action .= '<a href="' . route('estimates.create') . '?estimate=' . $row->id . '" class="dropdown-item"><i class="fa fa-copy mr-2"></i> ' . __('app.create') . ' ' . __('app.duplicate') . '</a>';
            }

            $action .= '</div>
            </div>
        </div>';

            return $action;
        });
        $datatables->addColumn('estimate_number', function ($row) {
            return '<a href="' . route('estimates.show', $row->id) . '" class="text-darkest-grey">' . $row->estimate_number . '</a>';
        });
        $datatables->addColumn('client_name', fn($row) => $row->name);
        $datatables->editColumn('name', fn($row) => view('components.client', ['user' => $row->client]));
        $datatables->editColumn('status', function ($row) {
            $status = '';

            if ($row->status == 'waiting') {
                $status .= '<i class="fa fa-circle mr-1 text-yellow f-10"></i>' . __('modules.estimates.' . $row->status) . '</label>';
            }
            elseif ($row->status == 'draft') {
                $status .= '<i class="fa fa-circle mr-1 text-blue f-10"></i>' . __('app.' . $row->status) . '</label>';
            }
            elseif ($row->status == 'canceled') {
                $status .= '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.' . $row->status) . '</label>';
            }
            elseif ($row->status == 'declined') {
                $status .= '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('modules.estimates.' . $row->status) . '</label>';
            }
            else {
                $status .= '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('modules.estimates.' . $row->status) . '</label>';
            }

            if (!$row->send_status && $row->status != 'draft' && $row->status != 'canceled') {
                $status .= ' <span class="badge badge-secondary my-2"> ' . __('modules.invoices.notSent') . '</span>';
            }

            return $status;
        });
        $datatables->addColumn('estimate_request_number', function ($row) {
            if ($row->estimate_request_id) {
                return '<a href="' . route('estimate-request.show', $row->estimate_request_id) . '" class="text-darkest-grey">' . $row->estimateRequest->estimate_request_number . '</a>';
            }
            else {
                return '--';
            }
        });
        $datatables->editColumn('total', fn($row) => currency_format($row->total, $row->currencyId));
        $datatables->editColumn('valid_till', fn($row) => Carbon::parse($row->valid_till)->translatedFormat($this->company->date_format));
        $datatables->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->translatedFormat($this->company->date_format));

        $datatables->removeColumn('currency_symbol');
        $datatables->removeColumn('client_id');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, Estimate::CUSTOM_FIELD_MODEL);

        $datatables->rawColumns(array_merge(['name', 'action', 'status', 'estimate_number', 'estimate_request_number'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $request = $this->request();

        $this->firstEstimate = Estimate::orderBy('id', 'desc')->first();
        $model = Estimate::with('client', 'client.session', 'company:id')
            ->join('client_details', 'estimates.client_id', '=', 'client_details.user_id')
            ->join('currencies', 'currencies.id', '=', 'estimates.currency_id')
            ->join('users', 'users.id', '=', 'estimates.client_id')
            ->leftJoin('invoices', 'invoices.estimate_id', '=', 'estimates.id')
            ->leftJoin('estimate_requests', 'estimate_requests.id', '=', 'estimates.estimate_request_id')
            ->select([
                'estimates.id',
                'estimates.company_id',
                'estimates.client_id',
                'users.name',
                'users.email',
                'estimates.total',
                'currencies.currency_symbol',
                'currencies.id as currencyId',
                'estimates.status',
                'estimates.valid_till',
                'estimates.estimate_number',
                'estimates.send_status',
                'estimates.added_by',
                'estimates.hash',
                'invoices.estimate_id',
                'estimates.created_at',
                'estimates.estimate_request_id',
                'estimate_requests.estimate_request_number',
            ]);

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $model = $model->where(DB::raw('DATE(estimates.`valid_till`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $model = $model->where(DB::raw('DATE(estimates.`valid_till`)'), '<=', $endDate);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $model = $model->where('estimates.status', '=', $request->status);
        }

        if ($request->clientID != 'all' && !is_null($request->clientID)) {
            $model = $model->where('estimates.client_id', '=', $request->clientID);
        }

        if (in_array('client', user_roles())) {
            $model = $model->where('estimates.send_status', 1);
            $model = $model->where('estimates.client_id', user()->id);
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('estimates.estimate_number', 'like', '%' . request('searchText') . '%')
                    ->orWhere('estimates.id', 'like', '%' . request('searchText') . '%')
                    ->orWhere('estimates.total', 'like', '%' . request('searchText') . '%')
                    ->orWhere(function ($query) {
                        $query->whereHas('client', function ($q) {
                            $q->where('name', 'like', '%' . request('searchText') . '%');
                        });
                    })
                    ->orWhere(function ($query) {
                        $query->where('estimates.status', 'like', '%' . request('searchText') . '%');
                    });
            });
        }

        if ($this->viewEstimatePermission == 'added') {
            $model->where('estimates.added_by', user()->id);
        }

        if ($this->viewEstimatePermission == 'both') {
            $model->where(function ($query) {
                $query->where('estimates.added_by', user()->id)
                    ->orWhere('estimates.client_id', user()->id);
            });
        }

        if ($this->viewEstimatePermission == 'owned') {
            $model->where('estimates.client_id', user()->id);
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

        $data = [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => false],
            __('app.estimate') . '#' => ['data' => 'estimate_number', 'name' => 'estimate_number', 'title' => __('app.estimate')],
            __('app.client') => ['data' => 'name', 'name' => 'users.name', 'exportable' => false, 'title' => __('app.client'), 'visible' => (in_array('clients', user_modules()) && !in_array('client', user_roles()))],
            __('app.customers') => ['data' => 'client_name', 'name' => 'users.name', 'visible' => false, 'title' => __('app.customers')],
            __('app.email') => ['data' => 'email', 'name' => 'users.email', 'visible' => false, 'title' => __('app.email')],
            __('modules.invoices.total') => ['data' => 'total', 'name' => 'total', 'title' => __('modules.invoices.total')],
            __('modules.estimates.validTill') => ['data' => 'valid_till', 'name' => 'valid_till', 'title' => __('modules.estimates.validTill')],
            __('app.createdOn') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.createdOn')],
            __('modules.estimateRequest.estimateRequest') . ' ' . __('app.number') => ['data' => 'estimate_request_number', 'name' => 'estimate_request_number', 'visible' => $this->showRequest , 'title' => __('modules.estimateRequest.estimateRequest') . ' ' . __('app.number')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')]
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new Estimate()), $action);

    }

}
