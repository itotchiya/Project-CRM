<?php

namespace App\Http\Controllers;

use App\DataTables\ExpensesDataTable;
use App\DataTables\RecurringExpensesDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Expenses\StoreRecurringExpense;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseRecurring;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class RecurringExpenseController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.expensesRecurring';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('expenses', $this->user->modules));

            return $next($request);
        });
    }

    public function index(RecurringExpensesDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->employees = User::allEmployees();
            $this->projects = Project::allProjects();
            $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        }

        return $dataTable->render('recurring-expenses.index', $this->data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('manage_recurring_expense');
        abort_403(!in_array($this->addPermission, ['all']));

        $this->currencies = Currency::all();
        $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        $this->projects = Project::all();
        $this->pageTitle = __('modules.expensesRecurring.addExpense');
        $this->projectId = request('project_id') ? request('project_id') : null;

        if (!is_null($this->projectId)) {
            $employees = Project::with('projectMembers')->where('id', $this->projectId)->first();
            $this->employees = $employees->projectMembers;

        } else {
            $this->employees = User::allEmployees();
        }

        $this->linkExpensePermission = user()->permission('link_expense_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', company()->currency_id);

        if($this->viewBankAccountPermission == 'added'){
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;

        if (request()->ajax()) {
            $html = view('recurring-expenses.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'recurring-expenses.ajax.create';
        return view('expenses.show', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRecurringExpense $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecurringExpense $request)
    {
        $expenseRecurring = new ExpenseRecurring();
        $expenseRecurring->item_name           = $request->item_name;
        $expenseRecurring->price               = round($request->price, 2);
        $expenseRecurring->currency_id         = $request->currency_id;
        $expenseRecurring->category_id         = $request->category_id;
        $expenseRecurring->user_id             = $request->user_id;
        $expenseRecurring->status              = $request->status;
        $expenseRecurring->rotation            = $request->rotation;
        $expenseRecurring->billing_cycle       = $request->billing_cycle > 0 ? $request->billing_cycle : null;
        $expenseRecurring->unlimited_recurring = $request->billing_cycle < 0 ? 1 : 0;
        $expenseRecurring->description         = trim_editor($request->description);
        $expenseRecurring->created_by          = $this->user->id;
        $expenseRecurring->purchase_from = $request->purchase_from;
        $expenseRecurring->issue_date = !is_null($request->issue_date) ? companyToYmd($request->issue_date) : now()->format('Y-m-d');

        if ($request->project_id > 0) {
            $expenseRecurring->project_id = $request->project_id;
        }

        if ($request->hasFile('bill')) {
            $filename = Files::uploadLocalOrS3($request->bill, Expense::FILE_PATH);
            $expenseRecurring->bill = $filename;
        }

        $expenseRecurring->immediate_expense = ($request->immediate_expense) ? 1 : 0;
        $expenseRecurring->bank_account_id = $request->bank_account_id;
        $expenseRecurring->status = 'active';
        $expenseRecurring->save();

        if($request->immediate_expense){

            $currency = Currency::where('id', $request->currency_id)->first();

            $expense = new Expense();
            $expense->expenses_recurring_id = $expenseRecurring->id;
            $expense->category_id = $request->category_id;
            $expense->project_id = $request->project_id;
            $expense->currency_id = $request->currency_id;
            $expense->user_id = $request->user_id;
            $expense->created_by = $expenseRecurring->created_by;
            $expense->item_name = $request->item_name;
            $expense->description = $request->description;
            $expense->price = $request->price;
            $expense->default_currency_id = company()->currency_id;
            $expense->exchange_rate = $currency->exchange_rate;
            $expense->purchase_from = $request->purchase_from;
            $expense->purchase_date = now()->format('Y-m-d');
            $expense->bank_account_id = $expenseRecurring->bank_account_id;
            $expense->status = 'approved';
            $expense->save();
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('recurring-expenses.show', $expenseRecurring->id);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->expense = ExpenseRecurring::with('recurrings')->findOrFail($id);

        $this->daysOfWeek = [
            '1' => 'sunday',
            '2' => 'monday',
            '3' => 'tuesday',
            '4' => 'wednesday',
            '5' => 'thursday',
            '6' => 'friday',
            '7' => 'saturday'
        ];

        $tab = request('tab');

        switch ($tab) {
        case 'expenses':
                return $this->expenses($id);
        default:
            $this->view = 'recurring-expenses.ajax.show';
            break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'overview';

        return view('recurring-expenses.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->addPermission = user()->permission('manage_recurring_expense');
        abort_403(!in_array($this->addPermission, ['all']));

        $this->expense = ExpenseRecurring::findOrFail($id);

        $this->currencies = Currency::all();
        $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        $this->pageTitle = __('modules.expensesRecurring.addExpense');
        $this->projectId = request('project_id') ? request('project_id') : null;

        $this->linkExpensePermission = user()->permission('link_expense_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', $this->expense->currency_id);

        if($this->viewBankAccountPermission == 'added'){
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;

        $userId = $this->expense->user_id;

        if (!is_null($userId)) {
            $this->projects = Project::with('members')->whereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->get();
        }
        else {
            $this->projects = Project::get();
        }

        if (!is_null($this->projectId)) {
            $employees = Project::with('projectMembers')->where('id', $this->projectId)->first();
            $this->employees = $employees->projectMembers;

        } else {
            $this->employees = User::allEmployees();
        }

        $this->view = 'recurring-expenses.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  StoreRecurringExpense  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreRecurringExpense $request, $id)
    {
        $expense = ExpenseRecurring::findOrFail($id);

        if($request->expense_count == 0)
        {
            $expense->item_name           = $request->item_name;
            $expense->price               = round($request->price, 2);
            $expense->currency_id         = $request->currency_id;
            $expense->category_id         = $request->category_id;
            $expense->user_id             = $request->user_id;
            $expense->rotation            = $request->rotation;
            $expense->billing_cycle       = $request->billing_cycle > 0 ? $request->billing_cycle : null;
            $expense->unlimited_recurring = $request->billing_cycle < 0 ? 1 : 0;
            $expense->description         = trim_editor($request->description);
            $expense->purchase_from       = $request->purchase_from;
            $expense->bank_account_id     = $request->bank_account_id;

            if(!is_null($request->issue_date)){
                $expense->issue_date =  companyToYmd($request->issue_date);
            }

            if ($request->project_id > 0) {
                $expense->project_id = $request->project_id;
            }

            if ($request->hasFile('bill')) {
                $filename = Files::uploadLocalOrS3($request->bill, Expense::FILE_PATH);
                $expense->bill = $filename;
            }

            $expense->save();
        }
        else {

            if (request()->has('status')) {
                $expense->status = $request->status;
            }

            $expense->save();
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('recurring-expenses.show', $expense->id);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->expense = ExpenseRecurring::findOrFail($id);
        $this->deletePermission = user()->permission('delete_expenses');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $this->expense->added_by == user()->id)));

        ExpenseRecurring::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function expenses($recurringID)
    {
        $dataTable = new ExpensesDataTable();
        $viewPermission = user()->permission('view_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->recurringID = $recurringID;
        $this->expense = ExpenseRecurring::findOrFail($recurringID);

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'recurring-expenses.ajax.expenses';

        return $dataTable->render('recurring-expenses.show', $this->data);
    }

    public function changeStatus(Request $request)
    {
        $expenseId = $request->expenseId;
        $status = $request->status;
        $expense = ExpenseRecurring::findOrFail($expenseId);
        $expense->status = $status;
        $expense->save();
        return Reply::success(__('messages.updateSuccess'));
    }

}
