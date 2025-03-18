<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Stripe\Stripe;
use App\Models\Tax;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\Currency;
use App\Models\Estimate;
use App\Models\Proposal;
use App\Models\UnitType;
use App\Models\BankAccount;
use App\Models\CreditNotes;
use App\Scopes\ActiveScope;
use App\Models\InvoiceItems;
use Illuminate\Http\Request;
use App\Models\ClientDetails;
use App\Models\CompanyAddress;
use App\Models\InvoiceSetting;
use App\Models\ProjectTimeLog;
use App\Events\NewInvoiceEvent;
use App\Models\ProductCategory;
use App\Models\InvoiceItemImage;
use App\Models\ProjectMilestone;
use Illuminate\Support\Facades\App;
use App\Events\PaymentReminderEvent;
use App\Events\NewPaymentEvent;
use App\Models\OfflinePaymentMethod;
use App\DataTables\InvoicesDataTable;
use App\Traits\EmployeeActivityTrait;
use App\Http\Requests\InvoiceFileStore;
use App\Models\PaymentGatewayCredentials;
use App\Http\Requests\Invoices\StoreInvoice;
use App\Http\Requests\Invoices\UpdateInvoice;
use App\Http\Requests\Payments\InvoicePayment;
use Modules\Purchase\Entities\PurchaseProduct;
use App\Http\Requests\Stripe\StoreStripeDetail;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use App\Http\Requests\Admin\Client\StoreShippingAddressRequest;
use App\Models\InvoicePaymentDetail;

class InvoiceController extends AccountBaseController
{
    use EmployeeActivityTrait;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.invoices';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('invoices', $this->user->modules));

            return $next($request);
        });
    }

    public function index(InvoicesDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_invoices');
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

        return $dataTable->render('invoices.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_invoices');

        $this->pageTitle = __('modules.invoices.addInvoice');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        if (request('invoice') != '') {
            $this->invoiceId = request('invoice');
            $this->type = 'invoice';
            $this->invoice = Invoice::with('items', 'client', 'client.projects', 'invoicePaymentDetail')->findOrFail($this->invoiceId);
        }


        // this data is sent from project and client invoices
        $this->project = request('project_id') ? Project::findOrFail(request('project_id')) : null;

        if (request('client_id')) {
            $this->client = User::withoutGlobalScope(ActiveScope::class)->findOrFail(request('client_id'));
        }

        if (request('estimate') != '') {
            $this->estimateId = request('estimate');
            $this->type = 'estimate';
            $this->estimate = Estimate::with('items', 'client', 'client.clientDetails', 'client.projects')->findOrFail($this->estimateId);
            $this->estimateCurrency = Currency::where('id', $this->estimate->currency_id)->first();
        }

        if (request('proposal') != '') {
            $this->proposalId = request('proposal');
            $this->type = 'proposal';
            $this->estimate = Proposal::with('items', 'lead', 'lead.contact')->findOrFail($this->proposalId);
            $this->client = $this->estimate->lead->contact->client;
            $this->proposalCurrency = Currency::where('id', $this->estimate->currency_id)->first();
        }

        $this->currencies = Currency::all();
        $this->categories = ProductCategory::all();
        $this->lastInvoice = Invoice::lastInvoiceNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->zero = '';

        if (strlen($this->lastInvoice) < $this->invoiceSetting->invoice_digit) {
            $condition = $this->invoiceSetting->invoice_digit - strlen($this->lastInvoice);

            for ($i = 0; $i < $condition; $i++) {
                $this->zero = '0' . $this->zero;
            }
        }

        $this->units = UnitType::all();
        $this->taxes = Tax::all();

        if (module_enabled('Purchase')){
            /** @phpstan-ignore-next-line */
            $this->products = Product::with('inventory')->get();
        }
        else
        {
            $this->products = Product::all();
        }

        $this->clients = User::allClients();
        $this->companyAddresses = CompanyAddress::all();
        $this->projects = Project::allProjectsHavingClient();
        $this->linkInvoicePermission = user()->permission('link_invoice_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');
        $this->paymentGateway = PaymentGatewayCredentials::first();
        $this->invoicePayments = InvoicePaymentDetail::all();


        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', company()->currency_id);

        if($this->viewBankAccountPermission == 'added'){
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;

        $this->companyCurrency = Currency::where('id', company()->currency_id)->first();

        if (request('type') == 'timelog' && in_array('projects', user_modules())) {

            $this->startDate = now($this->company->timezone)->subDays(7);
            $this->endDate = now($this->company->timezone);

            $this->view = 'invoices.ajax.create-timelog-invoice';

            if (request()->ajax()) {
                return $this->returnAjax($this->view);
            }

            return view('invoices.create', $this->data);
        }

        $invoice = new Invoice();

        $getCustomFieldGroupsWithFields = $invoice->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'invoices.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('invoices.create', $this->data);

    }

    public function store(StoreInvoice $request)
    {
        $quantity = $request->quantity;
        $product = $request->product_id;
        $stockAdjustment = [];

        if ((module_enabled('Purchase') && in_array('purchase', user_modules()) && $request->do_it_later == 'direct'))
        {
            if(is_array($product)) {

                $serviceProductIds = Product::whereIn('id', $product)->where('type', 'service')->pluck('id')->toArray();
                $nonServiceProductIds = array_diff($product, $serviceProductIds);

                foreach ($nonServiceProductIds as $key => $productId) {
                    if (!is_null($productId)) {
                        if(!isset($stockAdjustment[$productId])){
                            $stockAdjustment[$productId] = 0;
                        }


                    $stockAdjustment[$productId] += $quantity[$key];}
                }
            }

            $check = [];
            $invoiceItems = InvoiceItems::whereHas('invoice', function ($invoiceQuery) {
                $invoiceQuery->where('status', 'unpaid');
            })->get();

            foreach ($stockAdjustment as $index => $quantityCount)
            {
                $commitedStock = $invoiceItems->filter(function ($value, $key) use ($index) {
                    return $value->product_id == $index;
                })->sum('quantity');

                $quantity = PurchaseStockAdjustment::where('product_id', $index)->sum('net_quantity');

                if (($quantity - $commitedStock) < $quantityCount)
                {
                    $check[] = $index;

                }
            }

            if(!empty($check))
            {
                return Reply::dataOnly(['status' => 'error', 'data' => $check, 'showValue' => true, 'title' => $this->pageTitle]);
            }
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('invoices.index');
        }

        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $amount = $request->amount;

        if (empty($items)) {
            return Reply::error(__('messages.addItem'));
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('messages.itemBlank'));
            }
        }

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && (intval($qty) < 1)) {
                return Reply::error(__('messages.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('messages.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('messages.amountNumber'));
            }
        }

        $invoice = new Invoice();
        $invoice->project_id = $request->project_id ?? null;
        $invoice->client_id = ($request->client_id) ?: null;
        $invoice->issue_date = companyToYmd($request->issue_date);
        $invoice->due_date = companyToYmd($request->due_date);
        $invoice->sub_total = round($request->sub_total, 2);
        $invoice->discount = round($request->discount_value, 2);
        $invoice->discount_type = $request->discount_type;
        $invoice->total = round($request->total, 2);
        $invoice->due_amount = round($request->total, 2);
        $invoice->currency_id = $request->currency_id;
        $invoice->default_currency_id = company()->currency_id;
        $invoice->exchange_rate = $request->exchange_rate;
        $invoice->recurring = 'no';
        $invoice->billing_frequency = $request->recurring_payment == 'yes' ? $request->billing_frequency : null;
        $invoice->billing_interval = $request->recurring_payment == 'yes' ? $request->billing_interval : null;
        $invoice->billing_cycle = $request->recurring_payment == 'yes' ? $request->billing_cycle : null;
        $invoice->note = trim_editor($request->note);
        $invoice->show_shipping_address = $request->show_shipping_address;
        $invoice->invoice_number = $request->invoice_number;
        $invoice->company_address_id = $request->company_address_id;
        $invoice->estimate_id = $request->estimate_id ? $request->estimate_id : null;
        $invoice->bank_account_id = $request->bank_account_id;
        $invoice->payment_status = $request->payment_status == null ? '0' : $request->payment_status;
        $invoice->invoice_payment_id = $request->invoice_payment_id;
        $invoice->save();

        // To add custom fields data

        if ($request->custom_fields_data) {
            $invoice->updateCustomFieldData($request->custom_fields_data);
        }

        if ($request->estimate_id) {
            $estimate = Estimate::findOrFail($request->estimate_id);
            $estimate->status = 'accepted';
            $estimate->save();
        }

        if ($request->proposal_id) {
            $proposal = Proposal::findOrFail($request->proposal_id);
            $proposalData = [
                'invoice_convert' => 1,
            ];

            if ($proposal->signature) {
                $proposalData['status'] = 'accepted';
            }

            Proposal::where('id', $request->proposal_id)->update($proposalData);
        }

        if ($request->has('shipping_address')) {
            if ($invoice->project_id != null && $invoice->project_id != '') {
                $client = $invoice->project->clientdetails;
            }
            elseif ($invoice->client_id != null && $invoice->client_id != '') {
                $client = $invoice->clientdetails;
            }

            if (isset($client)) {
                $client->shipping_address = $request->shipping_address;

                $client->save();
            }
        }

        // Set milestone paid if converted milestone to invoice
        if ($request->milestone_id != '') {
            $milestone = ProjectMilestone::findOrFail($request->milestone_id);
            $milestone->invoice_created = 1;
            $milestone->invoice_id = $invoice->id;
            $milestone->save();
        }

        // Set invoice id in timelog
        if ($request->has('timelog_from') && $request->timelog_from != '' && $request->has('timelog_to') && $request->timelog_to != '') {
            $timelogFrom = companyToYmd($request->timelog_from);
            $timelogTo = companyToYmd($request->timelog_to);
            $this->timelogs = ProjectTimeLog::where('project_time_logs.project_id', $request->project_id)
                ->leftJoin('tasks', 'tasks.id', '=', 'project_time_logs.task_id')
                ->where('project_time_logs.earnings', '>', 0)
                ->where('project_time_logs.approved', 1)
                ->where(
                    function ($query) {
                        $query->where('tasks.billable', 1)
                            ->orWhereNull('tasks.billable');
                    }
                )
                ->whereDate('project_time_logs.start_time', '>=', $timelogFrom)
                ->whereDate('project_time_logs.end_time', '<=', $timelogTo)
                ->update(['invoice_id' => $invoice->id]);
        }

        // Log search
        $this->logSearchEntry($invoice->id, $invoice->invoice_number, 'invoices.show', 'invoice');

        if (user()) {
            self::createEmployeeActivity(user()->id, 'invoice-created', $invoice->id, 'invoice');
        }

        if ($invoice->send_status == 1) {
            return Reply::successWithData(__('messages.invoiceSentSuccessfully'), ['redirectUrl' => $redirectUrl, 'invoiceID' => $invoice->id]);
        }


        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl, 'invoiceID' => $invoice->id]);
    }

    public function committedModal(Request $request)
    {
        $productIds = $request->products;
        $productIDsArray = explode(',', $productIds);
        $this->products = PurchaseProduct::whereIn('id', $productIDsArray)->get();

        return view('invoices.ajax.comitted_model', $this->data);

    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_invoices') != 'all');

        $items = explode(',', $request->row_ids);

        foreach ($items as $id) {
            $firstInvoice = Invoice::orderBy('id', 'desc')->first();

            if ($firstInvoice->id == $id) {
                if (CreditNotes::where('invoice_id', $id)->exists()) {
                    CreditNotes::where('invoice_id', $id)->update(['invoice_id' => null]);
                }

                Invoice::destroy($id);

                return Reply::success(__('messages.deleteSuccess'));
            }
            else {
                return Reply::error(__('messages.invoiceCanNotDeleted'));
            }
        }
    }

    public function destroy($id)
    {
        $firstInvoice = Invoice::orderBy('id', 'desc')->first();
        $invoice = Invoice::findOrFail($id);
        $this->deletePermission = user()->permission('delete_invoices');
        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $invoice->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $invoice->client_id == user()->id)
            || ($this->deletePermission == 'both' && ($invoice->client_id == user()->id) || $invoice->added_by == user()->id)
        ));

        if ($firstInvoice->id == $id) {
            if (CreditNotes::where('invoice_id', $id)->exists()) {
                CreditNotes::where('invoice_id', $id)->update(['invoice_id' => null]);
            }

            Invoice::destroy($id);

            return Reply::success(__('messages.deleteSuccess'));
        }
        else {
            return Reply::error(__('messages.invoiceCanNotDeleted'));
        }
    }

    public function download($id)
    {
        $this->invoiceSetting = invoice_setting();
        $this->invoice = Invoice::with('project', 'items', 'items.unit')->findOrFail($id)->withCustomFields();

        $getCustomFieldGroupsWithFields = $this->invoice->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->viewPermission = user()->permission('view_invoices');
        $this->company = $this->invoice->company;

        $viewProjectInvoicePermission = user()->permission('view_project_invoices');
        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->invoice->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->invoice->client_id == user()->id)
            || ($viewProjectInvoicePermission == 'owned' && $this->invoice->project_id && $this->invoice->project->client_id == user()->id)
        ));

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        // Download file uploaded
        if ($this->invoice->file != null && request()->has('download-uploaded')) {
            return response()->download(storage_path('app/public/invoice-files') . '/' . $this->invoice->file);
        }

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf') : $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->invoice = Invoice::with('items', 'items', 'items.unit')->findOrFail($id)->withCustomFields();
        $this->invoiceSetting = InvoiceSetting::withoutGlobalScopes()->where('company_id', $this->invoice->company_id)->first();
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        $this->paidAmount = $this->invoice->getPaidAmount();
        $this->creditNote = 0;

        $getCustomFieldGroupsWithFields = $this->invoice->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        if ($this->invoice->credit_note) {
            $this->creditNote = CreditNotes::where('invoice_id', $id)
                ->select('cn_number')
                ->first();
        }

        $this->discount = 0;

        if ($this->invoice->discount > 0) {
            if ($this->invoice->discount_type == 'percent') {
                $this->discount = (($this->invoice->discount / 100) * $this->invoice->sub_total);
            }
            else {
                $this->discount = $this->invoice->discount;
            }
        }

        $taxList = array();

        $items = InvoiceItems::whereNotNull('taxes')->where('invoice_id', $this->invoice->id)->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = InvoiceItems::taxbyid($tax)->first();

                if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                    if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                    }
                    else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                    }

                }
                else {
                    if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                    }
                    else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->company = $this->invoice->company;

        $this->invoiceSetting = $this->company->invoiceSetting;

        $this->payments = Payment::with(['offlineMethod'])->where('invoice_id', $this->invoice->id)->where('status', 'complete')->orderByDesc('paid_on')->get();

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        // $pdf->loadView('invoices.pdf.' . $this->invoiceSetting->template, $this->data);
        $customCss = '<style>
                * { text-transform: none !important; }
            </style>';

        $pdf->loadHTML($customCss . view('invoices.pdf.' . $this->invoiceSetting->template, $this->data)->render());

        $filename = $this->invoice->invoice_number;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function domPdfObjectForConsoleDownload($id)
    {
        $this->invoice = Invoice::with('items')->findOrFail($id);
        $this->paidAmount = $this->invoice->getPaidAmount();
        $this->creditNote = 0;

        if ($this->invoice->credit_note) {
            $this->creditNote = CreditNotes::where('invoice_id', $id)
                ->select('cn_number')
                ->first();
        }

        if ($this->invoice->discount > 0) {
            if ($this->invoice->discount_type == 'percent') {
                $this->discount = (($this->invoice->discount / 100) * $this->invoice->sub_total);

            }
            else {
                $this->discount = $this->invoice->discount;
            }
        }
        else {
            $this->discount = 0;
        }

        $taxList = array();

        $items = InvoiceItems::whereNotNull('taxes')
            ->where('invoice_id', $this->invoice->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = InvoiceItems::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {
                        if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->company = $this->invoice->company;

        $this->invoiceSetting = $this->company->invoiceSetting;
        $this->payments = Payment::with(['offlineMethod'])->where('invoice_id', $this->invoice->id)->where('status', 'complete')->orderByDesc('paid_on')->get();
        $this->defaultAddress = CompanyAddress::where('is_default', 1)->where('company_id', $this->invoice->company_id)->first();

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        // Hide  $pdf->loadView('invoices.pdf.invoice-recurring', $this->data);
        $pdf->loadView('invoices.pdf.' . $this->invoiceSetting->template, $this->data);

        $filename = $this->invoice->invoice_number;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function edit($id)
    {
        $this->invoice = Invoice::with('client', 'client.projects', 'items', 'items.invoiceItemImage')->findOrFail($id)->withCustomFields();
        $this->editPermission = user()->permission('edit_invoices');
        $this->invoiceSetting = invoice_setting();

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->invoice->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->invoice->client_id == user()->id)
            || ($this->editPermission == 'both' && ($this->invoice->client_id == user()->id || $this->invoice->added_by == user()->id))
        ));

        abort_403($this->invoice->status == 'paid' && $this->invoice->amountPaid() > 0);

        $this->pageTitle = $this->invoice->invoice_number;

        $getCustomFieldGroupsWithFields = $this->invoice->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->projects = Project::whereNotNull('client_id')->get();
        $this->currencies = Currency::all();
        $this->categories = ProductCategory::all();
        $this->units = UnitType::all();

        $this->taxes = Tax::all();
        $this->products = Product::all();
        $this->clients = User::allClients();
        $this->linkInvoicePermission = user()->permission('link_invoice_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');
        $this->paymentGateway = PaymentGatewayCredentials::first();
        $this->methods = OfflinePaymentMethod::all();
        $this->invoicePayments = InvoicePaymentDetail::all();

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', $this->invoice->currency_id);

        if($this->viewBankAccountPermission == 'added'){
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;
        $this->companyCurrency = Currency::where('id', company()->currency_id)->first();

        if ($this->invoice->project_id != '') {
            $companyName = Project::where('id', $this->invoice->project_id)->with('clientdetails')->first();
            $this->companyName = isset($companyName) ? ($companyName->clientdetails ? $companyName->clientdetails->company_name : '') : '';
        }

        $this->companyAddresses = CompanyAddress::all();

        if (request()->ajax()) {
            $html = view('invoices.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'invoices.ajax.edit';

        return view('invoices.create', $this->data);

    }

    public function update(UpdateInvoice $request, $id)
    {
        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $product = $request->product_id;
        $amount = $request->amount;

        if (module_enabled('Purchase') && in_array('purchase', user_modules()) && $request->do_it_later == 'direct') {
            $stockAdjustment = [];

            if (is_array($product)) {

                $serviceProductIds = Product::whereIn('id', $product)->where('type', 'service')->pluck('id')->toArray();
                $nonServiceProductIds = array_diff($product, $serviceProductIds);

                foreach ($nonServiceProductIds as $key => $productId)
                {
                    if (!is_null($productId))
                    {    if(!isset($stockAdjustment[$productId])){
                        $stockAdjustment[$productId] = 0;
                    }

                    $stockAdjustment[$productId] += $quantity[$key];}
                }
            }

            $check = [];
            $invoiceItems = InvoiceItems::whereHas('invoice', function ($invoiceQuery) {
                $invoiceQuery->where('status', 'unpaid');
            })->get();

            foreach ($stockAdjustment as $index => $quantityCount)
            {
                $commitedStock = $invoiceItems->filter(function ($value, $key) use ($index) {
                    return $value->product_id == $index;
                })->sum('quantity');

                $qty = PurchaseStockAdjustment::where('product_id', $index)->sum('net_quantity');
                $productQuantity = InvoiceItems::select('quantity')->where('invoice_id', $id)->first();
                $productQty = $productQuantity->quantity;
                $remainingStock = $commitedStock - $productQty;

                if (($remainingStock + $quantityCount) > $qty)
                {
                    $check[] = $index;
                }
            }

            if(!empty($check && $productId))
            {
                return Reply::dataOnly(['status' => 'error', 'data' => $check, 'showValue' => true, 'title' => $this->pageTitle]);
            }
        }

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && $qty < 1) {
                return Reply::error(__('messages.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('messages.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('messages.amountNumber'));
            }
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('messages.itemBlank'));
            }
        }

        $invoice = Invoice::findOrFail($id);

        $invoice->project_id = $request->project_id ?? null;
        $invoice->client_id = ($request->client_id) ? $request->client_id : null;
        $invoice->issue_date = companyToYmd($request->issue_date);
        $invoice->due_date = companyToYmd($request->due_date);
        $invoice->sub_total = round($request->sub_total, 2);
        $invoice->discount = round($request->discount_value, 2);
        $invoice->discount_type = $request->discount_type;
        $invoice->total = round($request->total, 2);
        $invoice->due_amount = round($request->total, 2);
        $invoice->currency_id = $request->currency_id;
        $invoice->default_currency_id = company()->currency_id;
        $invoice->exchange_rate = $request->exchange_rate;

        if ($request->has('status')) {
            $invoice->status = $request->status;
        }

        $invoice->recurring = $request->recurring_payment;
        $invoice->billing_frequency = $request->recurring_payment == 'yes' ? $request->billing_frequency : null;
        $invoice->billing_interval = $request->recurring_payment == 'yes' ? $request->billing_interval : null;
        $invoice->billing_cycle = $request->recurring_payment == 'yes' ? $request->billing_cycle : null;
        $invoice->note = trim_editor($request->note);
        $invoice->show_shipping_address = $request->show_shipping_address;
        $invoice->invoice_number = $request->invoice_number;
        $invoice->company_address_id = $request->company_address_id;
        $invoice->bank_account_id = $request->bank_account_id;
        $invoice->payment_status = $request->payment_status == null ? '0' : $request->payment_status;
        $invoice->invoice_payment_id = $request->invoice_payment_id;
        $invoice->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $invoice->updateCustomFieldData($request->custom_fields_data);
        }

        if ($request->has('shipping_address')) {
            if ($invoice->project_id != null && $invoice->project_id != '') {
                $client = $invoice->project->clientdetails;
            }
            elseif ($invoice->client_id != null && $invoice->client_id != '') {
                $client = $invoice->clientdetails;
            }

            if (isset($client)) {
                $client->shipping_address = $request->shipping_address;
                $client->save();
            }
        }

        if (user()) {
            self::createEmployeeActivity(user()->id, 'invoice-updated', $invoice->id, 'invoice');
        }

        $redirectUrl = route('invoices.index');

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl, 'invoiceID' => $invoice->id]);
    }

    public function show($id)
    {
        $this->invoice = Invoice::with('project', 'items', 'items.unit', 'items.invoiceItemImage', 'invoicePaymentDetail')->findOrFail($id)->withCustomFields();
        /* Used for cancel invoice condition */
        $this->firstInvoice = Invoice::orderBy('id', 'desc')->first();

        $this->viewPermission = user()->permission('view_invoices');
        $this->deletePermission = user()->permission('delete_invoices');
        $viewProjectInvoicePermission = user()->permission('view_project_invoices');
        $this->addInvoicesPermission = user()->permission('add_invoices');

        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->invoice->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->invoice->client_id == user()->id && $this->invoice->send_status)
            || ($this->viewPermission == 'both' && ($this->invoice->added_by == user()->id || $this->invoice->client_id == user()->id))
            || ($viewProjectInvoicePermission == 'owned' && $this->invoice->client_id == user()->id && $this->invoice->send_status)
        ));

        $getCustomFieldGroupsWithFields = $this->invoice->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->paidAmount = $this->invoice->getPaidAmount();
        $this->pageTitle = $this->invoice->invoice_number;

        $this->firstInvoice = Invoice::orderBy('id', 'desc')->first();

        $this->discount = 0;

        if ($this->invoice->discount > 0) {
            if ($this->invoice->discount_type == 'percent') {
                $this->discount = (($this->invoice->discount / 100) * $this->invoice->sub_total);
            }
            else {
                $this->discount = $this->invoice->discount;
            }
        }

        if($this->invoice->discount_type == 'percent') {
            $discountAmount = $this->invoice->discount;
            $this->discountType = $discountAmount.'%';
        }else {
            $discountAmount = $this->invoice->discount;
            $this->discountType = currency_format($discountAmount, $this->invoice->currency_id);
        }

        $taxList = array();

        $items = InvoiceItems::whereNotNull('taxes')
            ->where('invoice_id', $this->invoice->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = InvoiceItems::taxbyid($tax)->first();

                if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                    if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                    }
                    else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                    }

                }
                else {
                    if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                    }
                    else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                    }
                }
            }
        }

        $this->taxes = $taxList;
        $this->payments = Payment::with(['offlineMethod'])->where('invoice_id', $this->invoice->id)->where('status', 'complete')->orderByDesc('paid_on')->get();

        $this->settings = company();
        $this->invoiceSetting = invoice_setting();
        $this->creditNote = 0;

        $this->credentials = PaymentGatewayCredentials::first();
        $this->methods = OfflinePaymentMethod::activeMethod();

        if(in_array('client', user_roles())) {
            $lastViewed = now();
            $ipAddress = request()->ip();
            $this->invoice->last_viewed = $lastViewed;
            $this->invoice->ip_address = $ipAddress;
            $this->invoice->save();
        }

        return view('invoices.show', $this->data);

    }

    public function approveOfflineInvoice($invoiceID)
    {
        $invoice = Invoice::with(['project', 'project.client', 'payment'])->findOrFail($invoiceID);

        if($invoice){

            $payment = Payment::findOrFail($invoice->payment[0]->id);

            if ($invoice->status == 'pending-confirmation') {

                $invoiceAmt = (float)($invoice->total);
                $paymentAmt = (float)($payment->amount);

                if($invoiceAmt > $paymentAmt){
                    $invoice->status = 'partial';
                }
                $invoice->status = 'paid';
            }

            $invoice->save();

            $payment->bank_account_id = $invoice->bank_account_id;
            $payment->status = 'complete';
            $payment->save();

            if ($invoice->project_id != null && $invoice->project_id != '') {
                $notifyUser = $invoice->project->client;
            }
            elseif ($invoice->client_id != null && $invoice->client_id != '') {
                $notifyUser = $invoice->client;
            }
            if (isset($notifyUser) && !is_null($notifyUser) ) {
                event(new NewPaymentEvent($payment, $notifyUser));
            }

            return Reply::success(__('messages.offlineInvoiceApproved'));
        }
    }

    public function sendInvoice($invoiceID)
    {
        $invoice = Invoice::with(['project', 'project.client'])->findOrFail($invoiceID);

        if ($invoice->project_id != null && $invoice->project_id != '') {
            $notifyUser = $invoice->project->client;
        }
        elseif ($invoice->client_id != null && $invoice->client_id != '') {
            $notifyUser = $invoice->client;
        }
        if (isset($notifyUser) && !is_null($notifyUser) && request()->data_type != 'mark_as_send') {
            event(new NewInvoiceEvent($invoice, $notifyUser));
        }

        $invoice->send_status = 1;

        if ($invoice->status == 'draft') {
            $invoice->status = 'unpaid';
        }

        $invoice->save();

        if(request()->data_type == 'mark_as_send'){
            return Reply::success(__('messages.invoiceMarkAsSent'));
        }

        else {
            return Reply::success(__('messages.invoiceSentSuccessfully'));
        }

    }

    public function remindForPayment($id)
    {
        $invoice = Invoice::with(['project', 'project.client'])->findOrFail($id);

        if ($invoice->project_id != null && $invoice->project_id != '') {
            $notifyUser = $invoice->project->client;
        }
        elseif ($invoice->client_id != null && $invoice->client_id != '') {
            $notifyUser = $invoice->client;
        }
        if (isset($notifyUser) && !is_null($notifyUser)) {
            event(new PaymentReminderEvent($invoice, $notifyUser));
        }

        return Reply::success('messages.reminderMailSuccess');
    }

    public function addItem(Request $request)
    {
        $this->items = Product::findOrFail($request->id);
        $this->invoiceSetting = invoice_setting();

        $exchangeRate = Currency::findOrFail($request->currencyId);

        if($exchangeRate->exchange_rate == $request->exchangeRate){
            $exRate = $exchangeRate->exchange_rate;
        }else{
            $exRate = floatval($request->exchangeRate ?: 1);
        }

        if (!is_null($exchangeRate) && !is_null($exchangeRate->exchange_rate)) {
            if ($this->items->total_amount != '') {
                /** @phpstan-ignore-next-line */
                $this->items->price = floor($this->items->total_amount / $exRate);
            }
            else {

                $this->items->price = floatval($this->items->price) / floatval($exRate);
            }
        }
        else {
            if ($this->items->total_amount != '') {
                $this->items->price = $this->items->total_amount;
            }
        }

        $this->items->price = number_format((float)$this->items->price, 2, '.', '');
        $this->taxes = Tax::all();
        $this->units = UnitType::all();
        $view = view('invoices.ajax.add_item', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function appliedCredits(Request $request, $id)
    {
        $this->invoice = Invoice::with('payment', 'payment.creditNote')->findOrFail($id);
        $this->pageTitle = __('app.menu.payments');

        $this->payments = $this->invoice->payment->filter(function ($payment) {
            return $payment->status === 'complete';
        });

        if (request()->ajax()) {
            $html = view('invoices.ajax.applied_credits', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'invoices.ajax.applied_credits';

        return view('invoices.create', $this->data);
    }

    public function deleteAppliedCredit(Request $request, $id)
    {

        $this->invoice = Invoice::with('payment', 'payment.creditNote')->findOrFail($request->invoice_id);

        $payment = Payment::with('creditNote', 'invoice')->findOrFail($id);
        $payment->delete();

        $creditNote = CreditNotes::find($payment->credit_notes_id);

        // Change credit note status
        if (isset($creditNote) && $creditNote->status == 'closed') {
            $creditNote->status = 'open';
            $creditNote->save();
        }


        $this->payments = $this->invoice->payment;

        if (request()->ajax()) {
            $view = view('invoices.ajax.applied_credits', $this->data)->render();

            return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view, 'remainingAmount' => number_format((float)$this->invoice->amountDue(), 2, '.', '')]);
        }

        return Reply::redirect(route('invoices.show', [$this->invoice->id]), __('messages.deleteSuccess'));
    }

    public function paymentDetail($invoiceID)
    {
        $this->invoice = Invoice::findOrFail($invoiceID);
        $this->pageTitle = __('app.menu.payments');

        if (request()->ajax()) {
            $html = view('invoices.ajax.payment-details', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'invoices.ajax.payment-details';

        return view('invoices.create', $this->data);
    }

    public function fileUpload()
    {
        $this->invoiceId = request('invoice_id');

        return view('invoices.file_upload', $this->data);
    }

    public function storeFile(InvoiceFileStore $request)
    {
        $invoiceId = $request->invoice_id;
        $file = $request->file('file');

        $newName = $file->hashName(); // Setting hashName name
        // Getting invoice data
        $invoice = Invoice::findOrFail($invoiceId);

        if ($invoice != null) {

            if ($invoice->file != null) {
                unlink(storage_path('app/public/invoice-files') . '/' . $invoice->file);
            }

            $file->move(storage_path('app/public/invoice-files'), $newName);

            $invoice->file = $newName;
            $invoice->file_original_name = $file->getClientOriginalName(); // Getting uploading file name;

            $invoice->save();

            return Reply::success('messages.fileUploadedSuccessfully');
        }

        return Reply::error(__('messages.fileUploadIssue'));
    }

    public function stripeModal(Request $request)
    {
        $this->invoiceID = $request->invoice_id;
        $this->countries = countries();

        return view('invoices.stripe.index', $this->data);
    }

    public function saveStripeDetail(StoreStripeDetail $request)
    {
        $id = $request->invoice_id;
        $this->invoice = Invoice::with(['client', 'project', 'project.client'])->findOrFail($id);
        $this->settings = $this->company;
        $this->credentials = PaymentGatewayCredentials::first();

        $client = null;

        if (!is_null($this->invoice->client_id)) {
            $client = $this->invoice->client;
        }
        else if (!is_null($this->invoice->project_id) && !is_null($this->invoice->project->client_id)) {
            $client = $this->invoice->project->client;
        }

        if (($this->credentials->test_stripe_secret || $this->credentials->live_stripe_secret) && !is_null($client)) {
            Stripe::setApiKey($this->credentials->stripe_mode == 'test' ? $this->credentials->test_stripe_secret : $this->credentials->live_stripe_secret);

            $totalAmount = $this->invoice->amountDue();

            $customer = \Stripe\Customer::create([
                'email' => $client->email,
                'name' => $request->clientName,
                'address' => [
                    'line1' => $request->clientName,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                ],
            ]);

            $intent = \Stripe\PaymentIntent::create([
                'amount' => $totalAmount * 100,
                'currency' => $this->invoice->currency->currency_code,
                'customer' => $customer->id,
                'setup_future_usage' => 'off_session',
                'payment_method_types' => ['card'],
                'description' => $this->invoice->invoice_number . ' Payment',
                'metadata' => ['integration_check' => 'accept_a_payment', 'invoice_id' => $id]
            ]);

            $this->intent = $intent;
        }

        $customerDetail = [
            'email' => $client->email,
            'name' => $request->clientName,
            'line1' => $request->clientName,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
        ];

        $this->customerDetail = $customerDetail;

        $view = view('invoices.stripe.stripe-payment', $this->data)->render();

        return Reply::dataOnly(['view' => $view, 'intent' => $this->intent]);
    }

    public function offlinePaymentModal(Request $request)
    {
        $this->invoiceID = $request->invoice_id;
        $this->methods = OfflinePaymentMethod::activeMethod();
        $this->invoice = Invoice::findOrFail($this->invoiceID);

        return view('invoices.offline.index', $this->data);
    }

    public function storeOfflinePayment(InvoicePayment $request)
    {
        $returnUrl = '';

        if (isset($request->invoiceID)) {
            $invoiceId = $request->invoiceID;
            $invoice = Invoice::findOrFail($request->invoiceID);
            $returnUrl = route('invoices.show', $invoiceId);
        }

        if (isset($request->orderID)) {
            $order = Order::findOrFail($request->orderID);
            $returnUrl = route('orders.show', $request->orderID);
        }

        $clientPayment = new Payment();
        $clientPayment->currency_id = isset($invoice) ? $invoice->currency_id : $order->currency_id;
        $clientPayment->invoice_id = isset($invoice) ? $invoice->id: null;
        $clientPayment->project_id = isset($invoice) ? $invoice->project_id : null;
        $clientPayment->order_id = $request->orderID;
        $clientPayment->amount = isset($invoice) ? $invoice->total : $order->total;
        $clientPayment->offline_method_id = ($request->offlineMethod != 'all') ? $request->offlineMethod : null;
        $clientPayment->gateway = 'Offline';
        $clientPayment->status = 'pending';
        $clientPayment->paid_on = now();

        if ($request->hasFile('bill')) {
            $clientPayment->bill = $request->bill->hashName();
            $request->bill->store(Payment::FILE_PATH);
        }

        $clientPayment->save();

        if(isset($invoice)){
            $invoice->status = 'pending-confirmation';
            $invoice->save();
        }

        return Reply::successWithData(__('messages.requestSent'), ['redirectUrl' => $returnUrl]);
    }

    public function makeInvoice($orderId)
    {
        /* Step1 -  Set order status paid */
        $order = Order::findOrFail($orderId);

        /* Step2 - Make an invoice related to recently paid order_id */
        $invoice = new Invoice();
        $invoice->order_id = $orderId;
        $invoice->client_id = $order->client_id;
        $invoice->sub_total = $order->sub_total;
        $invoice->total = $order->total;
        $invoice->currency_id = $order->currency_id;
        $invoice->status = 'paid';
        $invoice->note = trim_editor($order->note);
        $invoice->issue_date = now();
        $invoice->send_status = 1;
        $invoice->invoice_number = Invoice::lastInvoiceNumber() + 1;
        $invoice->due_amount = 0;
        $invoice->save();

        /* Step3 - Make invoice item & image entry */
        if (isset($order->items)) {
            foreach ($order->items as $item) /* @phpstan-ignore-line */ {
                // Save invoice item
                $invoiceItem = new InvoiceItems();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->item_name = $item->item_name;
                $invoiceItem->item_summary = $item->item_summary;
                $invoiceItem->type = $item->type;
                $invoiceItem->quantity = $item->quantity;
                $invoiceItem->unit_price = $item->unit_price;
                $invoiceItem->amount = $item->amount;
                $invoiceItem->hsn_sac_code = $item->hsn_sac_code;
                $invoiceItem->taxes = $item->taxes;
                $invoiceItem->save();

                // Save invoice item image
                if ($item->orderItemImage) {
                    $invoiceItemImage = new InvoiceItemImage();
                    $invoiceItemImage->invoice_item_id = $invoiceItem->id;
                    $invoiceItemImage->external_link = $item->orderItemImage->external_link;
                    $invoiceItemImage->save();
                }
            }
        }

        return $invoice;
    }

    public function cancelStatus(Request $request)
    {
        $invoice = Invoice::findOrFail($request->invoiceID);
        $invoice->status = 'canceled'; // update status as canceled
        $invoice->save();

        if (quickbooks_setting()->status && quickbooks_setting()->access_token != '') {
            $quickBooks = new QuickbookController();
            $quickBooks->voidInvoice($invoice);
        }

        optional($invoice->payment->first())->delete();
        return Reply::success(__('messages.updateSuccess'));
    }

    public function getClientOrCompanyName($projectID = '')
    {
        $this->projectID = $projectID;
        $this->currencies = Currency::all();

        if ($projectID == '') {
            $this->clients = User::allClients();
            $exchangeRate = company()->currency->exchange_rate;
            $currencyName = company()->currency->currency_code;
        }
        else {
            $this->client = Project::with('currency')->where('id', $projectID)->with('client')->first();
            $this->companyName = '';
            $this->clientId = '';

            if ($this->client) {
                $this->companyName = $this->client->client->name;
                $this->clientId = $this->client->client->id;
            }

            $exchangeRate = Currency::where('id', $this->client->currency_id)->pluck('exchange_rate')->toArray();
            $currencyName = $this->client->currency->currency_code;
        }

        $currency = view('invoices.currency_list', $this->data)->render();
        $list = view('invoices.client_or_company_name', $this->data)->render();

        return Reply::dataOnly(['html' => $list, 'currency' => $currency, 'exchangeRate' => $exchangeRate, 'currencyName' => $currencyName]);
    }

    public function fetchTimelogs(Request $request)
    {
        $this->taxes = Tax::all();
        $this->invoiceSetting = invoice_setting();
        $projectId = $request->projectId;
        $this->qtyVal = $request->qtyValue;
        $this->timelogs = [];
        $this->units = UnitType::all();

        if (!is_null($request->timelogFrom) && $request->timelogFrom != '') {
            $timelogFrom = companyToYmd($request->timelogFrom);
            $timelogTo = companyToYmd($request->timelogTo);
            $this->timelogs = ProjectTimeLog::with('task')
                ->leftJoin('tasks', 'tasks.id', '=', 'project_time_logs.task_id')
                ->groupBy('project_time_logs.task_id')
                ->where('project_time_logs.project_id', $projectId)
                ->where('project_time_logs.earnings', '>', 0)
                ->where('project_time_logs.approved', 1)
                ->where(
                    function ($query) {
                        $query->where('tasks.billable', 1)
                            ->orWhereNull('tasks.billable');
                    }
                )
                ->whereDate('project_time_logs.start_time', '>=', $timelogFrom)
                ->whereDate('project_time_logs.end_time', '<=', $timelogTo)
                ->selectRaw('project_time_logs.id, project_time_logs.task_id, sum(project_time_logs.earnings) as sum')
                ->get();
        }

        $html = view('invoices.timelog-item', $this->data)->render();

        return Reply::dataOnly(['html' => $html]);
    }

    public function checkShippingAddress()
    {
        if (request()->has('clientId')) {
            $user = User::findOrFail(request()->clientId);

            if (request()->showShipping == 'yes' && (is_null($user->clientDetails->shipping_address) || $user->clientDetails->shipping_address === '')) {
                $view = view('invoices.show_shipping_address_input')->render();

                return Reply::dataOnly(['view' => $view]);
            }
            else {
                return Reply::dataOnly(['show' => 'false']);
            }
        }
        else {
            return Reply::dataOnly(['switch' => 'off']);
        }
    }

    public function toggleShippingAddress(Invoice $invoice)
    {
        $invoice->show_shipping_address = ($invoice->show_shipping_address === 'yes') ? 'no' : 'yes';
        $invoice->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function shippingAddressModal(Invoice $invoice)
    {
        $clientId = $invoice->clientdetails ? $invoice->clientdetails->user_id : $invoice->project->clientdetails->user_id;

        return view('invoices.add_shipping_address', ['clientId' => $clientId]);
    }

    public function addShippingAddress(StoreShippingAddressRequest $request, $clientId)
    {
        $clientDetail = ClientDetails::where('user_id', $clientId)->first();
        $clientDetail->shipping_address = $request->shipping_address;
        $clientDetail->save();

        return Reply::success(__('messages.recordSaved'));
    }

    public function deleteInvoiceItemImage(Request $request)
    {
        $item = InvoiceItemImage::where('invoice_item_id', $request->invoice_item_id)->first();

        if ($item) {
            Files::deleteFile($item->hashname, InvoiceItemImage::FILE_PATH . '/' . $item->id . '/');
            $item->delete();
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function getExchangeRate($id)
    {
        $exchangeRate = Currency::where('id', $id)->pluck('exchange_rate')->toArray();
        return Reply::dataOnly(['status' => 'success', 'data' => $exchangeRate]);
    }

    public function getclients($id)
    {
        $unitId = UnitType::where('id', $id)->first();
        return Reply::dataOnly(['status' => 'success', 'type' => $unitId] );
    }

    public function productCategory(Request $request)
    {
        $categorisedProduct = Product::with('category');

        if (!is_null($request->id) && $request->id != 'null' && $request->id != '') {
            $categorisedProduct = $categorisedProduct->where('category_id', $request->id);
        }

        $categorisedProduct = $categorisedProduct->get();

        return Reply::dataOnly(['status' => 'success', 'data' => $categorisedProduct] );
    }

    public function offlineDescription(Request $request)
    {
        $id = $request->id;

        $offlineMethod = $id ? OfflinePaymentMethod::findOrFail($id) : '';
        $description = $offlineMethod ? '<span class="float-left">'.$offlineMethod->description.'</span>' : '';

        if($offlineMethod && $offlineMethod->image){
            $description .= '<span class="float-right"><img src="'.$offlineMethod->image_url.'" width="100px" height="100px"/></span>';
        }
        return Reply::dataOnly(['status' => 'success', 'description' => $description]);
    }

}
