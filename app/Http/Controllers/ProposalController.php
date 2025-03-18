<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tax;
use App\Models\Deal;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Proposal;
use App\Models\UnitType;
use App\Models\ProposalItem;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Events\NewProposalEvent;
use App\Models\ProposalTemplate;
use App\Models\ProposalItemImage;
use Illuminate\Support\Facades\App;
use App\Models\ProposalTemplateItem;
use App\DataTables\ProposalDataTable;
use App\Http\Requests\Proposal\StoreRequest;
use App\Models\Lead;

class ProposalController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.proposal';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));

            return $next($request);
        });
    }

    public function index(ProposalDataTable $dataTable)
    {
        abort_403($this->sidebarUserPermissions['view_lead_proposals'] == 5);

        if (!request()->ajax()) {
            $this->leads = Lead::allLeads();
        }

        return $dataTable->render('proposals.index', $this->data);
    }

    public function create()
    {
        $this->pageTitle = __('modules.proposal.createProposal');

        $this->addPermission = user()->permission('add_lead_proposals');
        $this->viewLeadPermission = user()->permission('view_lead');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->taxes = Tax::all();

        if (request('deal_id') != '') {
            $this->deal = Deal::findOrFail(request('deal_id'));
        }
        else {
            $leadContact = Lead::query();

            if($this->viewLeadPermission == 'added') {
                $this->leadContacts = $leadContact->where('added_by', user()->id)->get();
            }
            elseif($this->viewLeadPermission == 'both') {
                $this->leadContacts = $leadContact->where('added_by', user()->id)
                            ->orWhere('lead_owner', user()->id)->get();
            }
            elseif($this->viewLeadPermission == 'owned') {
                $this->leadContacts = $leadContact->where('lead_owner', user()->id)->get();
            }
            elseif($this->viewLeadPermission == 'none') {
                $this->leadContacts = collect();
            }elseif($this->viewLeadPermission == 'all') {
                $this->leadContacts = $leadContact->get();
            }

            if (count($this->leadContacts) > 0) {
                $this->deals = Deal::allLeads($this->leadContacts[0]->id);
            }
            else {
                $this->deals = Deal::allLeads();
            }
        }

        $this->units = UnitType::all();
        $this->template = ProposalTemplate::all();
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->currencies = Currency::all();
        $this->invoiceSetting = invoice_setting();

        $this->template = ProposalTemplate::all();
        $this->proposalTemplate = request('template') ? ProposalTemplate::findOrFail(request('template')) : null;
        $this->proposalTemplateItem = request('template') ? ProposalTemplateItem::with('proposalTemplateItemImage')->where('proposal_template_id', request('template'))->get() : null;



        $this->view = 'proposals.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('proposals.create', $this->data);
    }

    public function store(StoreRequest $request)
    {

        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $amount = $request->amount;

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

        $lastProposal = Proposal::lastProposalNumber() + 1;
        $invoiceSetting = invoice_setting();
        $zero = str_repeat('0', $invoiceSetting->proposal_digit - strlen($lastProposal));

        $originalNumber = $zero . $lastProposal;
        $proposalNumber = $invoiceSetting->proposal_prefix . $invoiceSetting->proposal_number_separator . $zero . $lastProposal;


        $proposal = new Proposal();
        $proposal->deal_id = $request->deal_id;
        $proposal->valid_till = companyToYmd($request->valid_till);
        $proposal->sub_total = $request->sub_total;
        $proposal->total = $request->total;
        $proposal->currency_id = $request->currency_id;
        $proposal->note = trim_editor($request->note);
        $proposal->discount = round($request->discount_value, 2);
        $proposal->discount_type = $request->discount_type;
        $proposal->status = 'waiting';
        $proposal->signature_approval = ($request->require_signature) ? 1 : 0;
        $proposal->description = trim_editor($request->description);
        $proposal->proposal_number = $proposalNumber;
        $proposal->original_proposal_number = $originalNumber;
        $proposal->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('proposals.index');
        }

        $this->logSearchEntry($proposal->id, $proposalNumber, 'proposals.show', 'proposal');

        return Reply::redirect($redirectUrl, __('messages.recordSaved'));
    }

    public function show($id)
    {
        $this->viewLeadProposalsPermission = user()->permission('view_lead_proposals');

        $this->invoice = Proposal::with('deal', 'items', 'unit', 'lead', 'items.proposalItemImage')->findOrFail($id);
        abort_403(!($this->viewLeadProposalsPermission == 'all' || ($this->viewLeadProposalsPermission == 'added' && $this->invoice->added_by == user()->id)));

        $this->pageTitle = $this->invoice->proposal_number;

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

        if($this->invoice->discount_type == 'percent') {
            $discountAmount = $this->invoice->discount;
            $this->discountType = $discountAmount.'%';
        }else {
            $discountAmount = $this->invoice->discount;
            $this->discountType = currency_format($discountAmount, $this->invoice->currency_id);
        }

        $taxList = array();

        $this->firstProposal = Proposal::orderBy('id', 'desc')->first();
        $items = ProposalItem::whereNotNull('taxes')
            ->where('proposal_id', $this->invoice->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = ProposalItem::taxbyid($tax)->first();

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

        $this->settings = company();
        $this->invoiceSetting = invoice_setting();

        return view('proposals.show', $this->data);
    }

    public function edit($id)
    {
        $this->pageTitle = __('modules.proposal.updateProposal');
        $this->taxes = Tax::all();
        $this->currencies = Currency::all();
        $this->proposal = Proposal::with('items', 'lead')->findOrFail($id);

        $this->units = UnitType::all();
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->invoiceSetting = invoice_setting();

        $this->view = 'proposals.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('proposals.create', $this->data);
    }

    public function update(StoreRequest $request, $id)
    {
        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $hsn_sac_code = $request->hsn_sac_code;
        $quantity = $request->quantity;
        $amount = $request->amount;
        $itemsSummary = $request->item_summary;
        $tax = $request->taxes;

        if ($request->has('item_name') && (trim($items[0]) == '' || trim($cost_per_item[0]) == '')) {
            return Reply::error(__('messages.addItem'));
        }

        if ($request->has('quantity')) {
            foreach ($quantity as $qty) {
                if (!is_numeric($qty)) {
                    return Reply::error(__('messages.quantityNumber'));
                }
            }
        }

        if ($request->has('cost_per_item')) {
            foreach ($cost_per_item as $rate) {
                if (!is_numeric($rate)) {
                    return Reply::error(__('messages.unitPriceNumber'));
                }
            }
        }

        if ($request->has('amount')) {
            foreach ($amount as $amt) {
                if (!is_numeric($amt)) {
                    return Reply::error(__('messages.amountNumber'));
                }
            }
        }

        if ($request->has('item_name')) {
            foreach ($items as $itm) {
                if (is_null($itm)) {
                    return Reply::error(__('messages.itemBlank'));
                }
            }
        }

        $proposal = Proposal::findOrFail($id);
        $proposal->deal_id = $request->deal_id;
        $proposal->valid_till = companyToYmd($request->valid_till);
        $proposal->sub_total = $request->sub_total;
        $proposal->total = $request->total;
        $proposal->currency_id = $request->currency_id;
        $proposal->status = $request->status;
        $proposal->note = trim_editor($request->note);
        $proposal->discount = round($request->discount_value, 2);
        $proposal->discount_type = $request->discount_type;
        $proposal->signature_approval = ($request->require_signature) ? 1 : 0;
        $proposal->description = trim_editor($request->description);
        $proposal->save();

        return Reply::redirect(route('proposals.show', $proposal->id), __('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $proposal = Proposal::findOrFail($id);
        $this->deleteLeadProposalsPermission = user()->permission('delete_lead_proposals');
        abort_403(!($this->deleteLeadProposalsPermission == 'all' || ($this->deleteLeadProposalsPermission == 'added' && $proposal->added_by == user()->id)));

        Proposal::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function sendProposal($id)
    {
        $proposal = Proposal::findOrFail($id);

        if (request()->data_type != 'mark_as_send') {
            event(new NewProposalEvent($proposal, 'new'));
        }

        $proposal->send_status = 1;

        $proposal->save();

        if (request()->data_type == 'mark_as_send') {
            return Reply::success(__('messages.proposalMarkAsSent'));
        }

        return Reply::success(__('messages.proposalSendSuccess'));

    }

    public function download($id)
    {
        $this->proposal = Proposal::with('unit')->findOrFail($id);
        $this->viewLeadProposalsPermission = user()->permission('view_lead_proposals');
        abort_403(!($this->viewLeadProposalsPermission == 'all' || ($this->viewLeadProposalsPermission == 'added' && $this->proposal->added_by == user()->id)));

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->invoiceSetting = invoice_setting();
        $this->proposal = Proposal::with('items', 'lead', 'lead.contact', 'currency')->findOrFail($id);
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        if ($this->proposal->discount > 0) {
            if ($this->proposal->discount_type == 'percent') {
                $this->discount = (($this->proposal->discount / 100) * $this->proposal->sub_total);
            }
            else {
                $this->discount = $this->proposal->discount;
            }
        }
        else {
            $this->discount = 0;
        }

        $taxList = array();

        $items = ProposalItem::whereNotNull('taxes')
            ->where('proposal_id', $this->proposal->id)
            ->get();

        $this->invoiceSetting = invoice_setting();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = ProposalItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($this->proposal->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->proposal->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {
                        if ($this->proposal->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->proposal->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->company = $this->proposal->company;

        $pdf = app('dompdf.wrapper');

        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        // $pdf->loadView('proposals.pdf.' . $this->invoiceSetting->template, $this->data);
        $customCss = '<style>
                * { text-transform: none !important; }
            </style>';

        $pdf->loadHTML($customCss . view('proposals.pdf.' . $this->invoiceSetting->template, $this->data)->render());

        $filename = __('modules.lead.proposal') . '-' . $this->proposal->id;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function deleteProposalItemImage(Request $request)
    {
        $item = ProposalItemImage::where('proposal_item_id', $request->invoice_item_id)->first();

        if ($item) {
            Files::deleteFile($item->hashname, 'proposal-files/' . $item->id . '/');
            $item->delete();
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function getclients($id)
    {
        $client_data = Product::where('unit_id', $id)->get();
        $unitId = UnitType::where('id', $id)->first();

        return Reply::dataOnly(['status' => 'success', 'data' => $client_data, 'type' => $unitId]);
    }

    public function addItem(Request $request)
    {
        $this->items = Product::findOrFail($request->id);
        $this->invoiceSetting = invoice_setting();

        $exchangeRate = Currency::findOrFail($request->currencyId);

        if (!is_null($exchangeRate) && !is_null($exchangeRate->exchange_rate)) {
            if ($this->items->total_amount != '') {
                /** @phpstan-ignore-next-line */
                $this->items->price = floor($this->items->total_amount / $exchangeRate->exchange_rate);
            }
            else {

                $this->items->price = floatval($this->items->price) / floatval($exchangeRate->exchange_rate);
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

}
