<?php

namespace App\DataTables;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class PaymentsDataTable extends BaseDataTable
{

    private $deletePaymentPermission;
    private $viewPaymentPermission;
    private $trashedData;

    public function __construct($trashedData = false)
    {
        parent::__construct();
        $this->deletePaymentPermission = user()->permission('delete_payments');
        $this->viewPaymentPermission = user()->permission('view_payments');
        $this->trashedData = $trashedData;
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

        return $datatables
            ->addColumn('check', function ($row) {
                if ($row->gateway == null || $row->gateway == 'Offline' || $row->status == 'failed') {
                    return '<input type="checkbox"  class="select-table-row disabled"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
                }

                return '<input type="checkbox"  class="select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">

                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('payments.show', $row->id) . '" class="openRightModal dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                $action .= '<a href="' . route('payments.download', $row->id) . '" class="dropdown-item"><i class="fa fa-download mr-2"></i>' . __('app.download') . '</a>';

                if (
                    ($this->deletePaymentPermission == 'all'
                        || ($this->deletePaymentPermission == 'added' && user()->id == $row->added_by)
                        || ($this->deletePaymentPermission == 'owned' && isset($row->invoice) && user()->id == $row->invoice->client_id)
                        || ($this->deletePaymentPermission == 'both' && isset($row->invoice) && (user()->id == $row->invoice->client_id && isset($row->added_by) && user()->id == $row->added_by))
                    )
                    && ($row->gateway == 'Offline' || $row->gateway == null || $row->status == 'failed' || is_null($row->transaction_id) )
                ) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-payment-id="' . $row->id . '">
                            <i class="fa fa-trash mr-2"></i>
                            ' . trans('app.delete') . '
                        </a>';
                }

                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->addColumn('short_code', function ($row) {
                if (is_null($row->project)) {
                    return '--';
                }

                return $row->project->project_short_code;

            })
            ->editColumn('project_id', function ($row) {
                if (is_null($row->project)) {
                    return '--';
                }

                return '<a class="text-darkest-grey" href="' . route('projects.show', $row->project_id) . '">' . $row->project->project_name . '</a>';
            })
            ->editColumn('invoice_number', function ($row) {
                if (!is_null($row->invoice_id) && !is_null($row->invoice)) {
                    return '<a class="text-darkest-grey" href="' . route('invoices.show', $row->invoice_id) . '">' . $row->invoice->invoice_number . '</a>';
                }

                return '--';
            })
            ->editColumn('client_id', function ($row) {
                $user = null;

                if (!is_null($row->invoice_id) && isset($row->invoice->client)) {
                    $user = $row->invoice->client;
                }
                elseif (!is_null($row->project_id) && isset($row->project->client)) {
                    $user = $row->project->client;
                }
                elseif (!is_null($row->order_id) && isset($row->order->client)) {
                    $user = $row->order->client;
                }

                return $user ? view('components.client', ['user' => $user]) : '--';
            })
            ->addColumn('client_name', function ($row) {
                if (!is_null($row->invoice_id) && isset($row->invoice->client)) {
                    return $row->invoice->client->name;
                }

                if (!is_null($row->project_id) && isset($row->project->client)) {
                    return $row->project->client->name;
                }

                if (!is_null($row->order_id) && isset($row->order->client)) {
                    return $row->order->client->name;
                }

                return '--';
            })
            ->editColumn('client_email', function ($row) {
                if (!is_null($row->invoice_id) && isset($row->invoice->client->email)) {
                    return '<a class="text-darkest-grey" href="' . route('clients.show', $row->invoice->client->id) . '">' . ucfirst($row->invoice->client->email) . '</a>'; /** @phpstan-ignore-line */
                }

                if (!is_null($row->project_id) && isset($row->project->client->email)) {
                    return '<a class="text-darkest-grey" href="' . route('clients.show', $row->project->client->id) . '">' . ucfirst($row->project->client->email) . '</a>'; /** @phpstan-ignore-line */
                }

                if (!is_null($row->order_id) && isset($row->order->client->email)) {
                    return '<a class="text-darkest-grey" href="' . route('clients.show', $row->order->client->id) . '">' . ucfirst($row->order->client->email) . '</a>'; /** @phpstan-ignore-line */
                }

                return '--';
            })
            ->editColumn('order_number', function ($row) {
                if (!is_null($row->order_id) && !is_null($row->order)) {
                    return '<a class="text-darkest-grey" href="' . route('orders.show', $row->order_id) . '">' . $row->order->order_number . '</a>';
                }

                return '--';
            })
            ->editColumn('status', function ($row) {
                $statusClass = match ($row->status) {
                    'pending' => 'text-yellow',
                    'failed' => 'text-red',
                    default => 'text-dark-green',
                };

                return '<i class="fa fa-circle mr-1 ' . $statusClass . ' f-10"></i>' . __('app.' . $row->status);
            })

            ->editColumn('amount', function ($row) {
                $currencyId = (isset($row->currency)) ? $row->currency->id : '';

                return currency_format($row->amount, $currencyId);
            })
            ->editColumn('paid_on', function ($row) {
                if (!is_null($row->paid_on)) {
                    return $row->paid_on->translatedFormat($this->company->date_format);
                }
                    return '--';
            }
            )
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['invoice', 'action', 'status', 'client_id', 'client_email', 'project_id', 'invoice_number', 'order_number', 'check'])
            ->removeColumn('invoice_id')
            ->removeColumn('currency_symbol')
            ->removeColumn('currency_code')
            ->removeColumn('project_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $request = $this->request();

        $model = Payment::with([
            'invoice.client',
            'invoice.client.clientDetails.company:id,logo,company_name',
            'order.client',
            'order.client.clientDetails.company:id,logo,company_name',
            'currency:id,currency_symbol,currency_code',
            'project:id,project_short_code,project_name'
        ])
            ->leftJoin('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->leftJoin('orders', 'orders.id', '=', 'payments.order_id')
            ->select('payments.id', 'payments.company_id', 'payments.project_id', 'payments.currency_id', 'payments.invoice_id', 'payments.amount', 'payments.status', 'payments.paid_on', 'payments.remarks', 'payments.bill', 'payments.added_by', 'payments.order_id', 'payments.gateway', 'payments.transaction_id');

        if (!$this->trashedData) {
            $model = $model->whereNull('projects.deleted_at');
        }

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $model = $model->where(DB::raw('DATE(payments.`paid_on`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $model = $model->where(DB::raw('DATE(payments.`paid_on`)'), '<=', $endDate);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $model = $model->where('payments.status', '=', $request->status);
        }

        if ($request->projectID != 'all' && !is_null($request->projectID)) {
            $model = $model->where('payments.project_id', '=', $request->projectID);
        }

        if ($request->clientID != 'all' && !is_null($request->clientID)) {
            $clientId = $request->clientID;
            $model = $model->where(function ($query) use ($clientId) {
                $query->where('projects.client_id', $clientId)
                    ->orWhere('invoices.client_id', $clientId)
                    ->orWhere('orders.client_id', $clientId);
            });
        }

        if (in_array('client', user_roles())) {
            $model = $model->where(function ($query) {
                $query->where('projects.client_id', user()->id)
                    ->orWhere('invoices.client_id', user()->id)
                    ->orWhere('orders.client_id', user()->id);
            });
        }

        if ($request->searchText != '') {
            $model = $model->where(function ($query) {
                $query->where('projects.project_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('payments.amount', 'like', '%' . request('searchText') . '%')
                    ->orWhere('invoices.id', 'like', '%' . request('searchText') . '%')
                    ->orWhere('projects.project_short_code', 'like', '%' . request('searchText') . '%');
            });
        }

        if ($this->viewPaymentPermission == 'added') {
            $model = $model->where('payments.added_by', user()->id);
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
        $dataTable = $this->setBuilder('payments-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["payments-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                  //
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
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => !showId(), 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'payments.id', 'title' => __('app.id'), 'visible' => showId()],
            __('modules.taskCode') => ['data' => 'short_code', 'name' => 'project_short_code', 'title' => __('modules.taskCode')],
            __('app.project') => ['data' => 'project_id', 'name' => 'project_id', 'title' => __('app.project'), 'width' => '10%'],
            __('app.invoice') . '#' => ['data' => 'invoice_number', 'name' => 'invoices.invoice_number', 'title' => __('app.invoice') . '#'],
            __('app.client') => ['data' => 'client_id', 'name' => 'client_id.name', 'orderable' => false, 'title' => __('app.client'), 'exportable' => false, 'visible' => (!in_array('client', user_roles()) && in_array('clients', user_modules()))],
            __('app.customers') => ['data' => 'client_name', 'name' => 'client_name', 'visible' => false, 'title' => __('app.client')],
            __('app.client_email') => ['data' => 'client_email', 'name' => 'client_email', 'visible' => false, 'title' => __('app.client_email')],
            __('app.order') . '#' => ['data' => 'order_number', 'name' => 'payments.order_id', 'title' => __('app.order') . '#'],
            __('modules.invoices.amount') => ['data' => 'amount', 'name' => 'amount', 'title' => __('modules.invoices.amount')],
            __('modules.payments.paidOn') => ['data' => 'paid_on', 'name' => 'paid_on', 'title' => __('modules.payments.paidOn')],
            __('modules.payments.paymentGateway') => ['data' => 'gateway', 'name' => 'gateway', 'title' => __('modules.payments.paymentGateway')],
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
