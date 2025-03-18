<?php

namespace App\DataTables;

use App\Models\Invoice;
use App\Models\Tax;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;

class SalesReportDataTable extends BaseDataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */

    public function dataTable($query) // phpcs:ignore
    {
        $taxes = Tax::all();

        $datatable = datatables()
            ->eloquent($query);

        $datatable->addColumn('paid_on', function ($row) {
            return $row->paid_on ? Carbon::parse($row->paid_on)->format($this->company->date_format) : '--';
        });
        $datatable->addColumn('invoice_number', function ($row) {
            return $row->custom_invoice_number ?: '--';
        });
        $datatable->addColumn('client_name', function ($row) {
            return $row->client ? $row->client->name : '--';
        });
        $datatable->addColumn('invoice_value', function ($row) {
            return $row->total ? currency_format($row->total, $row->currency_id) : '--';
        });
        $datatable->addColumn('bank_account', function ($row) {
            return !is_null($row->bankAccount) ? $row->bankAccount->bank_name : '--';
        });
        $datatable->addColumn('taxable_value', function ($row) {

            if ($row->calculate_tax == 'after_discount') {
                if ($row->discount_type == 'percent') {
                    $discountAmount = (($row->sub_total / 100) * $row->discount);
                    $discountedAmount = ($row->sub_total - $discountAmount);
                }
                else {
                    $discountedAmount = ($row->sub_total - $row->discount);
                }

                return currency_format($discountedAmount, $row->currency_id);

            }

            return currency_format($row->sub_total, $row->currency_id);

        });

        $datatable->addColumn('discount', function ($row) {
            if ($row->discount > 0) {
                if ($row->discount_type == 'percent') {
                    $discountAmount = (($row->sub_total / 100) * $row->discount);
                }
                else {
                    $discountAmount = $row->discount;
                }

                return currency_format($discountAmount, $row->currency_id);
            }

            return 0;

        });

        $datatable->addColumn('amount_paid', function ($row) {
            return currency_format($row->amountPaid(), $row->currency_id);
        });

        foreach ($taxes as $taxName) {
            $taxList = array();
            $discount = 0;
            $datatable->addColumn($taxName['tax_name'], function ($row) use ($taxName, $taxList, $discount, $taxes) {

                if ($row->discount > 0) {
                    if ($row->discount_type == 'percent') {
                        $discount = (($row->discount / 100) * $row->sub_total);
                    }
                    else {
                        $discount = $row->discount;
                    }
                }

                foreach ($row->items as $item) {

                    if (!is_null($item->taxes)) {
                        foreach (json_decode($item->taxes) as $taxId) {

                            $taxValue = $taxes->filter(function ($value, $key) use ($taxId) {
                                return $value->id == $taxId;
                            })->first();

                            if ($taxName['tax_name'] == $taxValue->tax_name) {
                                if (!isset($taxList[$taxValue->tax_name . ': ' . $taxValue->rate_percent . '%'])) {

                                    if ($row->calculate_tax == 'after_discount' && $discount > 0) {
                                        $taxList[$taxValue->tax_name . ': ' . $taxValue->rate_percent . '%'] = ($item->amount - ($item->amount / $row->sub_total) * $discount) * (floatval($taxValue->rate_percent) / 100);
                                    }
                                    else {
                                        $taxList[$taxValue->tax_name . ': ' . $taxValue->rate_percent . '%'] = $item->amount * (floatval($taxValue->rate_percent) / 100);
                                    }

                                }
                                else {

                                    if ($row->calculate_tax == 'after_discount' && $discount > 0) {
                                        $taxList[$taxValue->tax_name . ': ' . $taxValue->rate_percent . '%'] = $taxList[$taxValue->tax_name . ': ' . $taxValue->rate_percent . '%'] + (($item->amount - ($item->amount / $row->sub_total) * $discount) * (floatval($taxValue->rate_percent) / 100));

                                    }
                                    else {

                                        $taxList[$taxValue->tax_name . ': ' . $taxValue->rate_percent . '%'] = $taxList[$taxValue->tax_name . ': ' . $taxValue->rate_percent . '%'] + ($item->amount * (floatval($taxValue->rate_percent) / 100));
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($taxList as $key => $taxListValue) {
                    return currency_format($taxListValue, $row->currency_id);
                }

                return 0;
            });
        }

        $datatable->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id);
        $rawColumns = $taxes->pluck('tax_name')->toArray();
        $rawColumns[] = array_push($rawColumns, 'client_name', 'paid_on');
        $datatable->orderColumn('client_name', 'client_id $1');
        $datatable->orderColumn('paid_on', 'paid_on $1');
        $datatable->orderColumn('bank_account', 'paid_on $1');
        $datatable->orderColumn('invoice_value', 'total $1');
        $datatable->orderColumn('amount_paid', 'total $1');
        $datatable->orderColumn('taxable_value', 'total $1');
        $datatable->orderColumn('discount', 'total $1');
        $datatable->orderColumn('invoice_number', 'custom_invoice_number $1');
        $datatable->rawColumns($rawColumns)->make(true);

        return $datatable;
    }

    /**
     * @param Invoice $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Invoice $model)
    {
        $request = $this->request();

        $model = $model->with('items', 'client', 'bankAccount')->select('payments.paid_on', 'invoices.*')
            ->join('payments', 'payments.invoice_id', 'invoices.id')
            ->whereNotNull('payments.invoice_id');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);

            if (!is_null($startDate)) {
                $model = $model->where(DB::raw('DATE(payments.`created_at`)'), '>=', $startDate);
            }
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);

            if (!is_null($endDate)) {
                $model = $model->where(function ($query) use ($endDate) {
                    $query->where(DB::raw('DATE(payments.`created_at`)'), '<=', $endDate);
                });
            }
        }

        if ($request->clientID != 'all' && !is_null($request->clientID)) {
            $clientId = $request->clientID;
            $model = $model->where(function ($query) use ($clientId) {
                $query->where('invoices.client_id', $clientId);
            });
        }

        $model->groupBy('payments.id');

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('sales-report-table', 5)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["sales-report-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                //
                $(".select-picker").selectpicker();
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
        $columns = Tax::all();
        $newColumns = [];

        $newColumns['#'] = ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'];
        $newColumns[__('app.id')] = ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')];
        $newColumns[__('modules.payments.paidOn')] = ['data' => 'paid_on', 'name' => 'paid_on', 'title' => __('modules.payments.paidOn')];
        $newColumns[__('app.invoiceNumber')] = ['data' => 'invoice_number', 'name' => 'invoice_number', 'title' => __('app.invoiceNumber')];
        $newColumns[__('app.clientName')] = ['data' => 'client_name', 'name' => 'client_name', 'title' => __('app.clientName')];
        $newColumns[__('modules.invoices.invoiceValue')] = ['data' => 'invoice_value', 'name' => 'invoice_value', 'title' => __('modules.invoices.invoiceValue')];
        $newColumns[__('modules.invoices.amountPaid')] = ['data' => 'amount_paid', 'name' => 'amount_paid', 'title' => __('modules.invoices.amountPaid')];
        $newColumns[__('modules.invoices.taxableValue')] = ['data' => 'taxable_value', 'name' => 'taxable_value', 'title' => __('modules.invoices.taxableValue')];
        $newColumns[__('modules.invoices.discount')] = ['data' => 'discount', 'name' => 'discount', 'title' => __('modules.invoices.discount')];

        foreach ($columns as $column) {
            $newColumns[$column->tax_name] = ['data' => $column->tax_name, 'name' => $column->tax_name, 'orderable' => false, 'searchable' => false, 'visible' => true];
        }

        $newColumns[__('app.bankaccount')] = ['data' => 'bank_account', 'name' => 'bank_account', 'title' => __('app.bankaccount')];

        return $newColumns;
    }

}
