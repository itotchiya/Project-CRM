<?php

namespace App\Http\Controllers;

use App\DataTables\PaymentsDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Payments\StoreBulkPayments;
use App\Http\Requests\Payments\StorePayment;
use App\Http\Requests\Payments\UpdatePayments;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\OfflinePaymentMethod;
use App\Models\Payment;
use App\Models\PaymentGatewayCredentials;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\EmployeeActivityTrait;

class PaymentController extends AccountBaseController
{

    use EmployeeActivityTrait;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.payments';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('payments', $this->user->modules));

            return $next($request);
        });
    }

    public function index(PaymentsDataTable $dataTable)
    {

        $viewPermission = user()->permission('view_payments');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->projects = Project::allProjects();

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
            }
        }

        return $dataTable->render('payments.index', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_payments') != 'all');

        $items = explode(',', $request->row_ids);

        foreach ($items as $id) {

            $payment = ($id != 'on') ? Payment::findOrFail($id) : '';

            if ($payment && $payment != '') {
                $payment->delete();
            }
        }
    }

    protected function changeStatus($request)
    {
        abort_403(user()->permission('edit_payments') != 'all');

        Payment::whereIn('id', explode(',', $request->row_ids))->update(['status' => $request->status]);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_payments');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->pageTitle = __('modules.payments.addPayment');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        if (request()->has('default_client') && request('default_client') != '') {
            $this->defaultClient = request('default_client');
            $this->projects = Project::with('currency')->where('client_id', request('default_client'))->get();
        }
        else {
            $this->projects = Project::with('currency')->whereNotNull('client_id')->get();
        }

        $this->currencyCode = company()->currency->currency_code;
        $this->exchangeRate = company()->currency->exchange_rate;

        if (request()->has('project')) {
            $this->projectId = request()->project;
        }

        $this->project = request()->has('project') ? Project::findOrFail(request()->project) : null;

        if ($this->project) {
            $this->currencyCode = $this->project->currency->currency_code;
            $this->exchangeRate = $this->project->currency->exchange_rate;
        }

        $bankAccountQuery = BankAccount::query();

        if (request()->get('invoice_id')) {
            $this->invoice = Invoice::findOrFail(request()->get('invoice_id'));
            $this->paidAmount = $this->invoice->amountPaid();
            $this->unpaidAmount = $this->invoice->amountDue();
            $this->currencyCode = $this->invoice->currency->currency_code;
            $this->exchangeRate = $this->invoice->currency->exchange_rate;


            if ($this->invoice->project_id) {
                $this->project = Project::findOrFail($this->invoice->project_id);
            }

            $bankAccountQuery = $bankAccountQuery->where('status', 1)->where('currency_id', $this->invoice->currency_id);
        }
        elseif (request()->has('default_client') && request('default_client') != '') {
            $this->invoices = Invoice::with('payment', 'currency')
                ->where('client_id', request('default_client'))
                ->where('send_status', 1)
                ->where(function ($q) {
                    $q->where('status', 'unpaid')
                        ->orWhere('status', 'partial');
                })->get();
        }
        elseif (request()->has('project')) {
            $this->invoices = Invoice::with('payment')
                ->where('project_id', request('project'))
                ->where('send_status', 1)
                ->where(function ($q) {
                    $q->where('status', 'unpaid')
                        ->orWhere('status', 'partial');
                })->get();
        }
        else {
            $this->invoices = Invoice::with('payment')->where(function ($q) {
                $q->where('status', 'unpaid')
                    ->orWhere('status', 'partial');
            })
                ->where('send_status', 1)
                ->get();

            $bankAccountQuery = $bankAccountQuery->where('status', 1)->where('currency_id', company()->currency_id);
        }


        if ($this->viewBankAccountPermission == 'added') {
            $bankAccountQuery = $bankAccountQuery->where('added_by', user()->id);
            /* @phpstan-ignore-line */
        }

        $bankAccounts = $bankAccountQuery->get();
        $this->bankDetails = $bankAccounts;

        $this->currencies = Currency::all();
        $this->offlineMethod = OfflinePaymentMethod::all();

        $this->paymentGateway = PaymentGatewayCredentials::first();
        $this->linkPaymentPermission = user()->permission('link_payment_bank_account');
        $this->companyCurrency = Currency::where('id', company()->currency_id)->first();

        $this->view = 'payments.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('payments.create', $this->data);
    }

    public function store(StorePayment $request)
    {
        $payment = new Payment();

        if (!is_null($request->currency_id)) {
            $payment->currency_id = $request->currency_id;
        }
        else {
            $payment->currency_id = $this->company->currency_id;
        }

        if ($request->project_id != '') {
            $project = Project::findOrFail($request->project_id);
            $payment->project_id = $request->project_id;
            $payment->currency_id = $project->currency_id;
        }

        if ($request->invoice_id != '') {
            $invoice = Invoice::findOrFail($request->invoice_id);

            $payment->project_id = $invoice->project_id;
            $payment->invoice_id = $invoice->id;
            $payment->currency_id = $invoice->currency->id;

            $tolerance = 0.01;

            if ($request->amount > $invoice->amountDue() + $tolerance) {
                return Reply::error(__('messages.invoicePaymentExceedError'));
            }
        }

        $payment->default_currency_id = company()->currency_id;
        $payment->exchange_rate = $request->exchange_rate;
        $payment->amount = round($request->amount, 2);
        $payment->gateway = $request->gateway;
        $payment->transaction_id = $request->transaction_id;
        $payment->offline_method_id = $request->offline_methods;
        $payment->paid_on = companyToYmd($request->paid_on);

        $payment->remarks = $request->remarks;

        if ($request->hasFile('bill')) {
            $payment->bill = Files::uploadLocalOrS3($request->bill, Payment::FILE_PATH);
        }

        $payment->status = 'complete';
        $payment->bank_account_id = $request->bank_account_id;
        $payment->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('payments.index');
        }

        if (user()) {
            self::createEmployeeActivity(user()->id, 'payment-created', $payment->id, 'payment');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function destroy($id)
    {
        $payment = Payment::with('invoice')->findOrFail($id);
        $this->deletePermission = user()->permission('delete_payments');

        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $payment->added_by == user()->id)
            || ($this->deletePermission == 'owned' && user()->id == $payment->invoice->client_id)
            || ($this->deletePermission == 'both' && (user()->id == $payment->invoice->client_id && user()->id == $payment->added_by))
        ));

        if ($payment) {
            $payment->delete();
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function edit($id)
    {
        $this->payment = Payment::with('invoice', 'offlineMethods')->findOrFail($id);
        $this->editPermission = user()->permission('edit_payments');
        $this->methods = OfflinePaymentMethod::all();

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->payment->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->payment->invoice && $this->payment->invoice->client_id == user()->id)
            || ($this->editPermission == 'both' && (($this->payment->invoice && $this->payment->invoice->client_id == user()->id) || $this->payment->added_by == user()->id) || ($this->payment->gateway == null || $this->payment == 'Offline'))
        ));

        $this->pageTitle = __('modules.payments.updatePayment');
        $this->projects = Project::with('currency')->whereNotNull('client_id')->get();
        $this->currencies = Currency::all();
        $this->paymentGateway = PaymentGatewayCredentials::first();
        $this->companyCurrency = Currency::where('id', company()->currency_id)->first();

        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', $this->payment->currency_id);

        if ($this->viewBankAccountPermission == 'added') {
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;

        $this->invoices = Invoice::where(function ($query) {
            if (in_array('client', user_roles())) {
                $query->where('invoices.client_id', user()->id);
            }
            else {
                $query->where('invoices.project_id', $this->payment->project_id)
                    ->whereNotNull('invoices.project_id');
            }
        })->pending()->get();

        $this->linkPaymentPermission = user()->permission('link_payment_bank_account');

        $this->view = 'payments.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('payments.create', $this->data);
    }

    public function update(UpdatePayments $request, $id)
    {

        $payment = Payment::findOrFail($id);

        if ($request->project_id != '' && $request->project_id != '0') {
            $payment->project_id = $request->project_id;
        }
        else {
            $payment->project_id = null;
        }

        $payment->currency_id = $request->currency_id;
        $payment->default_currency_id = company()->currency_id;
        $payment->exchange_rate = $request->exchange_rate;
        $payment->amount = round($request->amount, 2);
        $payment->gateway = $request->gateway;
        $payment->offline_method_id = $request->offline_methods;
        $payment->transaction_id = $request->transaction_id;

        if ($request->paid_on != '') {
            $payment->paid_on = companyToYmd($request->paid_on);
        }
        else {
            $payment->paid_on = null;
        }

        $payment->status = $request->status;
        $payment->remarks = $request->remarks;

        if ($request->bill_delete == 'yes') {
            Files::deleteFile($payment->bill, Payment::FILE_PATH);
            $payment->bill = null;
        }

        if ($request->hasFile('bill')) {
            $payment->bill = Files::uploadLocalOrS3($request->bill, Payment::FILE_PATH);
        }

        if ($request->invoice_id != '') {
            $invoice = Invoice::findOrFail($request->invoice_id);
            $payment->project_id = $invoice->project_id;
            $payment->invoice_id = $invoice->id;
            $payment->currency_id = $invoice->currency->id;
        }
        else {
            $payment->invoice_id = null;
        }

        $payment->bank_account_id = $request->bank_account_id;

        $payment->save();

        // change invoice status if exists
        if ($payment->invoice) {
            if ($payment->invoice->amountDue() <= 0) {
                $payment->invoice->status = 'paid';
            }
            else if ($payment->invoice->amountDue() >= $payment->invoice->total) {
                $payment->invoice->status = 'unpaid';
            }
            else {
                $payment->invoice->status = 'partial';
            }

            $payment->invoice->save();
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('payments.index')]);
    }

    public function show($id)
    {
        $this->payment = Payment::with(['invoice', 'project', 'currency', 'offlineMethods', 'transactions' => function ($q) {
            $q->orderByDesc('id')->limit(1);
        }, 'transactions.bankAccount'])->findOrFail($id);

        $this->viewPermission = user()->permission('view_payments');

        abort_403(!($this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->payment->added_by == user()->id)
            || ($this->viewPermission == 'owned' && !is_null($this->payment->project_id) && $this->payment->project->client_id == user()->id)
            || ($this->viewPermission == 'owned' && !is_null($this->payment->invoice_id) && $this->payment->invoice->client_id == user()->id)
            || ($this->viewPermission == 'owned' && !is_null($this->payment->order_id) && $this->payment->order->client_id == user()->id)
            || ($this->viewPermission == 'both' && ($this->payment->added_by == user()->id || (!is_null($this->payment->project_id) && $this->payment->project->client_id == user()->id) || (!is_null($this->payment->invoice_id) && $this->payment->invoice->client_id == user()->id) || (!is_null($this->payment->order_id) && $this->payment->order->client_id == user()->id)))
        ));

        $this->pageTitle = __('modules.payments.paymentDetails');
        $this->view = 'payments.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('payments.create', $this->data);
    }

    public function download($id)
    {
        $this->invoiceSetting = invoice_setting();

        $this->payment = Payment::with('invoice', 'project', 'currency')->findOrFail($id);
        $this->viewPermission = user()->permission('view_payments');

        abort_403(!($this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->payment->added_by == user()->id)
            || ($this->viewPermission == 'owned' && !is_null($this->payment->project_id) && $this->payment->project->client_id == user()->id)
            || ($this->viewPermission == 'owned' && !is_null($this->payment->invoice_id) && $this->payment->invoice->client_id == user()->id)
            || ($this->viewPermission == 'owned' && !is_null($this->payment->order_id) && $this->payment->order->client_id == user()->id)
        ));

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->invoiceSetting = invoice_setting();
        $this->payment = Payment::with('invoice', 'project', 'currency')->findOrFail($id);

        $this->settings = company();

        $this->invoiceSetting = invoice_setting();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('payments.ajax.pdf', $this->data);
        $filename = __('app.menu.payments') . ' ' . $this->payment->id;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function accountList(Request $request)
    {
        $options = '<option value="">--</option>';

        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', $request->curId);

        if ($this->viewBankAccountPermission == 'added') {
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();

        foreach ($bankAccounts as $bankAccount) {

            $bankName = '';

            if ($bankAccount->type == 'bank') {
                $bankName = $bankAccount->bank_name . ' |';
            }

            $options .= '<option value="' . $bankAccount->id . '"> ' . $bankName . ' ' . $bankAccount->account_name . ' </option>';
        }

        if($request->paymentInvoiceId){
            $exchangeRate = Invoice::where('id', $request->paymentInvoiceId)->pluck('exchange_rate')->toArray();
        }else{
            $exchangeRate = Currency::where('id', $request->curId)->pluck('exchange_rate')->toArray();
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'exchangeRate' => $exchangeRate]);
    }

    public function offlineMethods()
    {
        $offlineMethod = OfflinePaymentMethod::all();

        return Reply::dataOnly(['status' => 'success', 'data' => $offlineMethod]);
    }

    public function addBulkPayments()
    {
        $this->addPermission = user()->permission('add_payments');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->pageTitle = __('modules.payments.addBulkPayment');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $clientId = in_array('client', user_roles()) ? $this->user->id : request()->client_id;

        $this->pendingPayments = Invoice::with(['bankAccount', 'currency', 'payment'])
            ->where(function ($q) {
                $q->where('status', 'unpaid');
                $q->orWhere('status', 'partial');
            });

        if ($clientId != 'all' && $clientId != null) {
            $this->pendingPayments = $this->pendingPayments->where('client_id', $clientId)->get();
        }
        else {
            $this->pendingPayments = $this->pendingPayments->get();
        }

        $this->paymentGateway = PaymentGatewayCredentials::first();
        $this->offlineMethods = OfflinePaymentMethod::where('status', 'yes')->get();
        $this->linkPaymentPermission = user()->permission('link_payment_bank_account');
        $this->companyCurrency = Currency::where('id', $this->company->currency_id)->first();

        $bankAccounts = BankAccount::where('status', 1);

        if ($this->viewBankAccountPermission == 'added') {
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;

        if (in_array('client', user_roles())) {

            $this->clients = User::client();
        }
        else {
            $this->clients = User::allClients();
        }

        $this->paymentID = request()->payment_id;
        $this->offlineID = request()->offline_id;

        if (request()->ajax() && (($clientId == 'all' || $clientId != null) || ($this->paymentID == 'all' || $this->paymentID != null) || ($this->offlineID == 'all' || $this->offlineID != null))) {
            $table = view('payments.ajax.bulk-payments', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'table' => $table, 'payment' => $this->paymentID]);
        }

        $this->view = 'payments.ajax.add-bulk-payments';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('payments.create', $this->data);
    }

    public function saveBulkPayments(StoreBulkPayments $request)
    {
        $invoiceIds = $request->invoice_number;

        // Give error if no data was selected in any of the field
        $insertRecord = 0;
        $totalRecord = count($invoiceIds);

        foreach ($invoiceIds as $index => $invoiceId) {
            $gateway = $request->gateway[$index];

            if ($gateway == 'all') {
                $insertRecord = $insertRecord + 1;
            }
        }

        if ($totalRecord === $insertRecord) {
            return Reply::error(__('messages.pleaseEnterSomeData'));
        }

        DB::beginTransaction();

        foreach ($invoiceIds as $index => $invoiceId) {
            $amount = $request->amount[$index];

            if ($amount > 0 && ($amount != 0 || $amount != '0')) {

                $invoice = Invoice::findOrFail($invoiceId);

                if ($amount > $invoice->amountDue()) {
                    return Reply::error(__('messages.invoicePaymentExceedError'));
                }

                $paidAmount = $invoice->amountPaid();
                $gateway = $request->gateway[$index];
                $transaction_id = $request->transaction_id[$index];
                $bank_account_id = $request->bank_account_id[$index];
                $offline_method_id = $request->offline_method_id[$index];
                $payment_date = $request->payment_date[$index] ? Carbon::createFromFormat($this->company->date_format, $request->payment_date[$index])->format('Y-m-d') : null;

                $payment = new Payment();

                $payment->gateway = $gateway;
                $payment->status = 'complete';
                $payment->paid_on = $payment_date;
                $payment->invoice_id = $invoice->id;
                $payment->amount = round($amount, 2);
                $payment->transaction_id = $transaction_id;
                $payment->project_id = $invoice->project_id;
                $payment->bank_account_id = $bank_account_id;
                $payment->exchange_rate = $invoice->exchange_rate;
                $payment->default_currency_id = company()->currency_id;
                $payment->offline_method_id = ($gateway == 'Offline') ? $offline_method_id : null;
                $payment->currency_id = $invoice->currency ? $invoice->currency->id : $this->company->currency_id;

                $payment->save();

                if ((float)($paidAmount + $amount) >= (float)$invoice->total) {
                    $invoice->status = 'paid';
                    $invoice->payment_status = '1';
                }
                else {
                    $invoice->status = 'partial';
                }

                $invoice->save();
            }
        }

        DB::commit();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('payments.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

}
