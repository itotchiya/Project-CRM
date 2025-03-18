<?php

namespace App\DataTables;

use App\Models\BankAccount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class BankAccountDataTable extends BaseDataTable
{

    private $editBankAccountPermission;
    private $deleteBankAccountPermission;
    private $viewBankAccountPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editBankAccountPermission = user()->permission('edit_bankaccount');
        $this->deleteBankAccountPermission = user()->permission('delete_bankaccount');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');
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

                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('bankaccounts.show', $row->id) . '" class=" dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editBankAccountPermission == 'all' || ($this->editBankAccountPermission == 'added' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('bankaccounts.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteBankAccountPermission == 'all' || ($this->deleteBankAccountPermission == 'added' && user()->id == $row->added_by)) {
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
            ->editColumn('bank_name', function ($row) {
                return $row->type == 'bank' ? $row->bank_name : '--';
            })
            ->editColumn('bank_name_logo', function ($row) {
                $bankLogo = '';

                if ($row->bank_logo) {
                    $bankLogo = '<img data-toggle="tooltip" src="' . $row->file_url . '" class="width-35 height-35 mr-2 img-fluid">';

                }
                else {
                    $bankLogo = $row->file_url;
                }

                return '<a class="text-darkest-grey" href="' . route('bankaccounts.show', $row->id) . '">' . $bankLogo . ' ' . $row->bank_name . '</a>';
            })
            ->editColumn('account_name', function ($row) {
                return '<a class="text-darkest-grey" href="' . route('bankaccounts.show', $row->id) . '">' . $row->account_name . '</a>';
            })
            ->editColumn('account_type', function ($row) {
                return $row->type == 'bank' ? __('modules.bankaccount.'.$row->account_type) : '--';
            })
            ->editColumn('type', function ($row) {
                return $row->type ? __('modules.bankaccount.'.$row->type) : '--';
            })
            ->addColumn('currency', function ($row) {
                return $row->currency->currency_code . ' (' . $row->currency->currency_symbol . ')';
            })
            ->editColumn('status', function ($row) {
                if ($this->editBankAccountPermission == 'all' || ($this->editBankAccountPermission == 'added' && user()->id == $row->added_by)) {
                    $status = '<select class="form-control select-picker change-account-status" data-account-id="' . $row->id . '">';
                    $status .= '<option ';

                    if ($row->status == '1') {
                        $status .= 'selected';
                    }

                    $status .= ' value="1" data-content="<i class=\'fa fa-circle mr-2 text-light-green\'></i> ' . __('app.active') . '">' . __('app.active') . '</option>';

                    $status .= '<option ';

                    if ($row->status == '0') {
                        $status .= 'selected';
                    }

                    $status .= ' value="0" data-content="<i class=\'fa fa-circle mr-2 text-red\'></i> ' . __('app.inactive') . '">' . __('app.inactive') . '</option>';

                    $status .= '</select>';

                    return $status;
                }
                else {
                    if ($row->status == '1') {
                        return '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.active');
                    }
                    else {
                        return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.inactive');
                    }
                }

            })
            ->addColumn('account_status', function ($row) {
                return $row->status == '1' ? __('app.active') : __('app.inactive');
            })
            ->editColumn('bank_balance', function ($row) {
                return currency_format($row->bank_balance, $row->currencyId);
            })
            ->addColumn('bank_balance_export', function ($row) {
                return $row->bank_balance;
            })

            ->editColumn('id', function ($row) {
                return $row->id;
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'account_name', 'status', 'check', 'bank_name_logo']);
    }

    /**
     * @param BankAccount $model
     * @return BankAccount|\Illuminate\Database\Eloquent\Builder
     */
    public function query(BankAccount $model)
    {
        $request = $this->request();

        $model = BankAccount::with('currency')->select('bank_accounts.*', 'currencies.currency_symbol', 'currencies.id as currencyId')
            ->join('currencies', 'currencies.id', 'bank_accounts.currency_id');

        if (!is_null($request->searchText)) {
            $model = $model->where(function ($query) {
                $query->where('bank_accounts.account_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('bank_accounts.account_type', 'like', '%' . request('searchText') . '%')
                    ->orWhere('bank_accounts.contact_number', 'like', '%' . request('searchText') . '%')
                    ->orWhere('bank_accounts.bank_name', 'like', '%' . request('searchText') . '%');
            });

        }

        if ($request->type != 'all' && !is_null($request->type)) {
            $model = $model->where('bank_accounts.type', '=', $request->type);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $model = $model->where('bank_accounts.status', '=', $request->status);
        }

        if ($request->accountId != 'all' && !is_null($request->accountId)) {
            $model = $model->where('bank_accounts.id', '=', $request->accountId);
        }

        if ($request->accountType != 'all' && !is_null($request->accountType)) {
            $model = $model->where('bank_accounts.account_type', '=', $request->accountType);
        }

        if ($request->bankName != 'all' && !is_null($request->bankName)) {
            $model = $model->where('bank_accounts.bank_name', '=', $request->bankName);
        }

        if ($this->viewBankAccountPermission == 'added') {
            $model->where('bank_accounts.added_by', user()->id);
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
        $dataTable = $this->setBuilder('bank-account-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["bank-account-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $(".change-account-status").selectpicker();
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
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            __('modules.bankaccount.bankName') => ['data' => 'bank_name', 'name' => 'bank_name', 'title' => __('modules.bankaccount.bankName'), 'visible' => false],
            __('app.menu.bankaccount') => ['data' => 'bank_name_logo', 'name' => 'bank_name', 'title' => __('app.menu.bankaccount'), 'exportable' => false],
            __('modules.bankaccount.accountName') => ['data' => 'account_name', 'name' => 'account_name', 'title' => __('modules.bankaccount.accountName')],
            __('modules.bankaccount.accountType') => ['data' => 'account_type', 'name' => 'account_type', 'title' => __('modules.bankaccount.accountType')],
            __('modules.bankaccount.type') => ['data' => 'type', 'name' => 'type', 'title' => __('modules.bankaccount.type')],
            __('app.currency') => ['data' => 'currency', 'name' => 'currency', 'title' => __('app.currency')],
            __('modules.bankaccount.bankBalance') => ['data' => 'bank_balance', 'name' => 'bank_balance', 'title' => __('modules.bankaccount.bankBalance'), 'exportable' => false],
            __('modules.bankaccount.bankBalance') . 'export'  => ['data' => 'bank_balance_export', 'name' => 'bank_balance', 'title' => __('modules.bankaccount.bankBalance'), 'visible' => false],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status'), 'exportable' => false],
            __('modules.bankaccount.accountStatus') => ['data' => 'account_status', 'name' => 'status', 'title' => __('modules.bankaccount.accountStatus'), 'visible' => false],
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
