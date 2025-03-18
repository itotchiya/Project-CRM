<?php

namespace App\DataTables;

use App\Models\Expense;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class ExpensesDataTable extends BaseDataTable
{

    private $editExpensePermission;
    private $deleteExpensePermission;
    private $viewExpensePermission;
    private $approveExpensePermission;
    private $includeSoftDeletedProjects;

    public function __construct($includeSoftDeletedProjects = false)
    {
        parent::__construct();
        $this->editExpensePermission = user()->permission('edit_expenses');
        $this->deleteExpensePermission = user()->permission('delete_expenses');
        $this->viewExpensePermission = user()->permission('view_expenses');
        $this->approveExpensePermission = user()->permission('approve_expenses');
        $this->includeSoftDeletedProjects = $includeSoftDeletedProjects;
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
        $datatables->addColumn('check', fn($row) => $this->checkBox($row));
        $datatables->addColumn('action', function ($row) {

            $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('expenses.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

            if (is_null($row->expenses_recurring_id)) {
                if ($this->editExpensePermission == 'all' || ($this->editExpensePermission == 'added' && user()->id == $row->added_by)) {
                    if (is_null($row->project_id)) {
                        $action .= '<a class="dropdown-item openRightModal" href="' . route('expenses.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                                </a>';
                    } else if (!is_null($row->project_id) && is_null($row->project_deleted_at)) {
                        $action .= '<a class="dropdown-item openRightModal" href="' . route('expenses.edit', [$row->id]) . '">
                            <i class="fa fa-edit mr-2"></i>
                            ' . trans('app.edit') . '
                            </a>';
                    }
                }

                if ($this->deleteExpensePermission == 'all' || ($this->deleteExpensePermission == 'added' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-expense-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });
        $datatables->editColumn('price', fn($row) => $row->total_amount);

        $datatables->editColumn('item_name', function ($row) {
            $link = '<a href="' . route('expenses.show', $row->id) . '" class="openRightModal text-darkest-grey">' . $row->item_name . '</a>';
            return is_null($row->expenses_recurring_id) ? $link : "$link <p class='mb-0'><span class='badge badge-primary'>" . __('app.recurring') . "</span></p>";
        });

        $datatables->addColumn('export_item_name', fn($row) => $row->item_name);

        $datatables->addColumn('employee_name', fn($row) => $row->user?->name);

        $datatables->editColumn('user_id', fn($row) => view('components.employee', ['user' => $row->user]));

        $datatables->editColumn('status', function ($row) {
            if (
                $this->approveExpensePermission != 'none'
                && (
                    $this->approveExpensePermission == 'all'
                    || ($this->approveExpensePermission == 'added' && $row->added_by == user()->id)
                    || ($this->approveExpensePermission == 'owned' && $row->user_id == user()->id)
                    || ($this->approveExpensePermission == 'both' && ($row->user_id == user()->id || $row->added_by == user()->id))
                )
            ) {


                $status = '<select class="form-control select-picker change-expense-status" data-expense-id="' . $row->id . '">';
                $status .= '<option ';

                if ($row->status == 'pending') {
                    $status .= 'selected';
                }

                $status .= ' value="pending" data-content="<i class=\'fa fa-circle mr-2 text-yellow\'></i> ' . __('app.pending') . '">' . __('app.pending') . '</option>';
                $status .= '<option ';

                if ($row->status == 'approved') {
                    $status .= 'selected';
                }

                $status .= ' value="approved" data-content="<i class=\'fa fa-circle mr-2 text-light-green\'></i> ' . __('app.approved') . '"' . __('app.approved') . '</option>';
                $status .= '<option ';

                if ($row->status == 'rejected') {
                    $status .= 'selected';
                }

                $status .= ' value="rejected" data-content="<i class=\'fa fa-circle mr-2 text-red\'></i> ' . __('app.rejected') . '">' . __('app.rejected') . '</option>';

                $status .= '</select>';

            }
            else {
                if ($row->status == 'pending') {
                    $class = 'text-yellow';
                    $status = __('app.pending');

                }
                else if ($row->status == 'approved') {
                    $class = 'text-light-green';
                    $status = __('app.approved');

                }
                else {
                    $class = 'text-red';
                    $status = __('app.rejected');
                }

                $status = '<i class="fa fa-circle mr-1 ' . $class . ' f-10"></i> ' . $status;
            }

            return $status;
        });
        $datatables->addColumn('status_export', fn($row) => $row->status);
        $datatables->editColumn('purchase_date', fn($row) => $row->purchase_date?->translatedFormat($this->company->date_format));
        $datatables->editColumn('purchase_from', fn($row) => $row->purchase_from ?? '--');
        $datatables->smart(false);
        $datatables->setRowId(fn($row) => 'row-' . $row->id);
        $datatables->addIndexColumn();
        $datatables->removeColumn('currency_id');
        $datatables->removeColumn('name');
        $datatables->removeColumn('currency_symbol');
        $datatables->removeColumn('updated_at');
        $datatables->removeColumn('created_at');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, Expense::CUSTOM_FIELD_MODEL);

        $datatables->rawColumns(array_merge(['action', 'status', 'user_id', 'item_name', 'check'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $request = $this->request();

        $model = Expense::with('currency', 'user', 'user.employeeDetail', 'user.employeeDetail.designation', 'user.session')
            ->select('expenses.id', 'expenses.project_id', 'expenses.item_name', 'expenses.user_id', 'expenses.price', 'users.salutation', 'users.name', 'expenses.purchase_date', 'expenses.currency_id', 'currencies.currency_symbol', 'expenses.status', 'expenses.purchase_from', 'expenses.expenses_recurring_id', 'designations.name as designation_name', 'expenses.added_by', 'projects.deleted_at as project_deleted_at')
            ->leftjoin('users', 'users.id', 'expenses.user_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
            ->leftJoin('projects', 'projects.id', 'expenses.project_id')
            ->join('currencies', 'currencies.id', 'expenses.currency_id');

        if (!$this->includeSoftDeletedProjects) {
            $model->whereNull('projects.deleted_at');
        }

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $model = $model->where(DB::raw('DATE(expenses.`purchase_date`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $model = $model->where(DB::raw('DATE(expenses.`purchase_date`)'), '<=', $endDate);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $model = $model->where('expenses.status', '=', $request->status);
        }

        if ($request->employee != 'all' && !is_null($request->employee)) {
            $model = $model->where('expenses.user_id', '=', $request->employee);
        }

        if ($request->projectId != 'all' && !is_null($request->projectId)) {
            $model = $model->where('expenses.project_id', '=', $request->projectId);
        }

        if ($request->categoryId != 'all' && !is_null($request->categoryId)) {
            $model = $model->where('expenses.category_id', '=', $request->categoryId);
        }

        if ($request->recurringID != '') {
            $model = $model->where('expenses.expenses_recurring_id', '=', $request->recurringID);
        }

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('expenses.item_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('users.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('expenses.price', 'like', '%' . request('searchText') . '%')
                    ->orWhere('expenses.purchase_from', 'like', '%' . request('searchText') . '%');
            });
        }

        if ($this->viewExpensePermission == 'added') {
            $model->where('expenses.added_by', user()->id);
        }

        if ($this->viewExpensePermission == 'owned') {
            $model->where('expenses.user_id', user()->id);
        }

        if ($this->viewExpensePermission == 'both') {
            $model->where(function ($query) {
                $query->where('expenses.added_by', user()->id)
                    ->orWhere('expenses.user_id', user()->id);
            });
        }

        return $model->groupBy('expenses.id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('expenses-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["expenses-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $(".change-expense-status").selectpicker();
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
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => !showId(),'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'expenses.id', 'title' => __('app.id'),'visible' => showId()],
            __('modules.expenses.itemName') => ['data' => 'item_name', 'name' => 'item_name', 'exportable' => false, 'title' => __('modules.expenses.itemName')],
            __('app.menu.itemName') => ['data' => 'export_item_name', 'name' => 'export_item_name', 'visible' => false, 'title' => __('modules.expenses.itemName')],
            __('app.price') => ['data' => 'price', 'name' => 'price', 'title' => __('app.price')],
            __('app.menu.employees') => ['data' => 'user_id', 'name' => 'user_id', 'exportable' => false, 'title' => __('app.menu.employees')],
            __('app.employee') => ['data' => 'employee_name', 'name' => 'user_id', 'visible' => false, 'title' => __('app.employee')],
            __('modules.expenses.purchaseFrom') => ['data' => 'purchase_from', 'name' => 'purchase_from', 'title' => __('modules.expenses.purchaseFrom')],
            __('modules.expenses.purchaseDate') => ['data' => 'purchase_date', 'name' => 'purchase_date', 'title' => __('modules.expenses.purchaseDate')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'exportable' => false, 'title' => __('app.status')],
            __('app.expense') . ' ' . __('app.status') => ['data' => 'status_export', 'name' => 'status', 'visible' => false, 'title' => __('app.expense')]
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new Expense()), $action);

    }

}
