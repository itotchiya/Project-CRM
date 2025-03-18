<?php

namespace App\DataTables;

use App\Models\BankTransaction;
use Yajra\DataTables\Html\Column;

class BankTransactionDataTable extends BaseDataTable
{

    private $editBankTransactionPermission;
    private $deleteBankTransactionPermission;
    private $viewBankTransactionPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editBankTransactionPermission = user()->permission('edit_bankaccount');
        $this->deleteBankTransactionPermission = user()->permission('delete_bankaccount');
        $this->viewBankTransactionPermission = user()->permission('view_bankaccount');
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
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('bankaccounts.view_transaction', $row->id) . '" class="openRightModal dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('account_name', function ($row) {
                return '<a class="text-darkest-grey" href="' . route('bankaccounts.view_transaction', $row->id) . '">' . $row->account_name . '</a>';
            })
            ->editColumn('amount', function ($row) {
                return currency_format($row->amount, $row->currencyId);
            })
            ->editColumn('transaction_date', function ($row) {
                if (!is_null($row->transaction_date)) {
                    return $row->transaction_date->translatedFormat($this->company->date_format);
                }
            })
            ->addColumn('transaction_type', function ($row) {
                if ($row->type == 'Cr') {
                    return '<span class="badge badge-success">' . __('modules.bankaccount.credit') . '</span>';

                }
                else {
                    return '<span class="badge badge-danger">' . __('modules.bankaccount.debit') . '</span>';
                }
            })
            ->editColumn('bank_balance', function ($row) {
                return currency_format($row->bank_balance, $row->currencyId);
            })
            ->editColumn('title', function ($row) {

                if ($row->transaction_relation == 'expense') {
                    $title = __('modules.bankaccount.' . $row->title) . ' ( ' . $row->transaction_related_to . ' )';
                }
                elseif ($row->transaction_relation == 'payment') {
                    $title = __('modules.bankaccount.' . $row->title) . ' ( ' . $row->transaction_relation . '-' . $row->transaction_related_to . ' )';
                }
                else {
                    $title = __('modules.bankaccount.' . $row->title);
                }

                return $title;
            })
            ->editColumn('id', function ($row) {
                return $row->id;
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->orderColumn('transaction_date', 'transaction_date $1')
            ->rawColumns(['action', 'check', 'account_name', 'status', 'transaction_type']);
    }

    /**
     * @param BankTransaction $model
     * @return BankTransaction|\Illuminate\Database\Eloquent\Builder
     */
    public function query(BankTransaction $model)
    {
        $request = $this->request();

        $model = BankTransaction::with('bankAccount')
            ->select('bank_transactions.*', 'bank_accounts.account_name', 'bank_accounts.status', 'currencies.currency_symbol', 'currencies.id as currencyId')
            ->join('bank_accounts', 'bank_accounts.id', 'bank_transactions.bank_account_id')
            ->join('currencies', 'currencies.id', 'bank_accounts.currency_id')
            ->where('bank_transactions.bank_account_id', $request->bankId);

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {

        $dataTable = $this->setBuilder('bank-transaction-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["bank-transaction-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                  //
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
        $data = [
            /*'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],*/
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            __('modules.bankaccount.accountName') => ['data' => 'account_name', 'name' => 'account_name', 'title' => __('modules.bankaccount.accountName'), 'visible' => false],
            __('app.amount') => ['data' => 'amount', 'name' => 'amount', 'title' => __('app.amount')],
            __('modules.tickets.type') => ['data' => 'type', 'name' => 'type', 'title' => __('modules.tickets.type'), 'visible' => false],
            __('modules.bankaccount.bankTransaction') . ' ' . __('modules.tickets.type') => ['data' => 'transaction_type', 'name' => 'type', 'title' => __('modules.bankaccount.bankTransaction') . ' ' . __('modules.tickets.type'), 'exportable' => false],
            __('modules.bankaccount.transactionDate') => ['data' => 'transaction_date', 'name' => 'transaction_date', 'title' => __('modules.bankaccount.transactionDate')],
            __('modules.bankaccount.bankBalance') => ['data' => 'bank_balance', 'name' => 'bank_balance', 'title' => __('modules.bankaccount.bankBalance')],
            __('app.title') => ['data' => 'title', 'name' => 'title', 'title' => __('app.title')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return $data;
    }

}
