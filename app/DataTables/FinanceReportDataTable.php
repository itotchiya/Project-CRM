<?php

namespace App\DataTables;

use App\Models\Payment;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;

class FinanceReportDataTable extends BaseDataTable
{

    private $editPaymentPermission;
    private $deletePaymentPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editPaymentPermission = user()->permission('edit_payments');
        $this->deletePaymentPermission = user()->permission('delete_payments');
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
            ->editColumn('project_id', function ($row) {
                if (!is_null($row->project)) {
                    return '<a class="text-darkest-grey" href="' . route('projects.show', $row->project_id) . '">' . $row->project->project_name . '</a>';
                }
                else {
                    return '--';
                }
            })
            ->editColumn('invoice_number', function ($row) {
                if ($row->invoice_id != null) {
                    return '<a class="text-darkest-grey" href="' . route('invoices.show', $row->invoice_id) . '">' . $row->invoice->invoice_number . '</a>';
                }
                else {
                    return '--';
                }
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'pending') {
                    return '<i class="fa fa-circle mr-1 text-yellow f-10"></i>' . __('app.' . $row->status);
                }
                else {
                    return '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.' . $row->status);
                }
            })
            ->editColumn('amount', function ($row) {
                $currencyId = (!is_null($row->currency)) ? $row->currency->id : '';

                return currency_format($row->amount, $currencyId);
            })
            ->addColumn('default_currency_price', function ($row) {
                return currency_format($row->default_currency_price, company()->currency_id);
            })
            ->editColumn(
                'paid_on',
                function ($row) {
                    if (!is_null($row->paid_on)) {
                        return $row->paid_on->translatedFormat($this->company->date_format);
                    }
                }
            )
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['invoice', 'status', 'project_id', 'invoice_number'])
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

        $model = Payment::with(['project:id,project_name', 'currency:id,currency_symbol,currency_code', 'invoice'])
            ->leftJoin('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->select('payments.id', 'payments.project_id', 'payments.currency_id', 'payments.invoice_id', 'payments.amount', 'payments.status', 'payments.paid_on', 'payments.remarks', 'payments.bill', 'payments.added_by', 'payments.exchange_rate');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $model = $model->where(DB::raw('DATE(payments.`paid_on`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $model = $model->where(DB::raw('DATE(payments.`paid_on`)'), '<=', $endDate);
        }

        if ($request->projectID != 'all' && !is_null($request->projectID)) {
            $model = $model->where('payments.project_id', '=', $request->projectID);
        }

        if ($request->clientID != 'all' && !is_null($request->clientID)) {
            $clientId = $request->clientID;
            $model = $model->where(function ($query) use ($clientId) {
                $query->where('projects.client_id', $clientId)
                    ->orWhere('invoices.client_id', $clientId);
            });
        }

        if ($request->searchText != '') {
            $model = $model->where(function ($query) {
                $query->where('projects.project_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('payments.amount', 'like', '%' . request('searchText') . '%')
                    ->orWhere('invoices.id', 'like', '%' . request('searchText') . '%');
            });
        }

        $model = $model->where('payments.status', '=', 'complete');
        $model = $model->orderBy('payments.paid_on', 'desc');

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('payments-table')
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
        $defaultCurrency = Company::with('currency')->find(company()->id);
        return [
            '#' => ['data' => 'DT_RowIndex', 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.project') => ['data' => 'project_id', 'name' => 'project_id', 'title' => __('app.project')],
            __('app.invoice') . '#' => ['data' => 'invoice_number', 'name' => 'invoice.invoice_number', 'title' => __('app.invoice')],
            __('modules.invoices.amount') => ['data' => 'amount', 'name' => 'amount', 'title' => __('modules.invoices.amount')],
            __('modules.invoices.amount') . $defaultCurrency->currency->currency_code => ['data' => 'default_currency_price', 'name' => 'default_currency_price',  'orderable' => false, 'title' => __('modules.invoices.amount') . ' ( ' . $defaultCurrency->currency->currency_code . ' )'],
            __('modules.payments.paidOn') => ['data' => 'paid_on', 'name' => 'paid_on', 'title' => __('modules.payments.paidOn')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')]
        ];
    }

}
