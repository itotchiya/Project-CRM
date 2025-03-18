<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Tax;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Estimate;
use App\Models\UnitType;
use App\Models\EstimateItem;
use App\Models\InvoiceItems;
use Illuminate\Http\Request;
use App\Models\AcceptEstimate;
use App\Models\ProductCategory;
use App\Events\NewEstimateEvent;
use App\Models\EstimateTemplate;
use App\Models\EstimateItemImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Requests\StoreEstimate;
use App\Models\EstimateTemplateItem;
use Illuminate\Support\Facades\File;
use App\DataTables\EstimatesDataTable;
use App\Http\Requests\EstimateAcceptRequest;
use App\Models\EstimateRequest;

class EstimateController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.estimates';
        $this->pageIcon = 'ti-file';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('estimates', $this->user->modules));

            return $next($request);
        });
    }

    public function index(EstimatesDataTable $dataTable)
    {
        abort_403(!in_array(user()->permission('view_estimates'), ['all', 'added', 'owned', 'both']));

        return $dataTable->render('estimates.index', $this->data);

    }

    public function create()
    {
        $this->addPermission = user()->permission('add_estimates');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        if (request('estimate') != '') {
            $this->estimateId = request('estimate');
            $this->type = 'estimate';
            $this->estimate = Estimate::with('items', 'items.estimateItemImage', 'client', 'unit', 'client.projects')->findOrFail($this->estimateId);
        }

        $this->pageTitle = __('modules.estimates.createEstimate');
        $this->clients = User::allClients();
        $this->currencies = Currency::all();
        $this->lastEstimate = Estimate::lastEstimateNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->zero = '';

        if (strlen($this->lastEstimate) < $this->invoiceSetting->estimate_digit) {
            $condition = $this->invoiceSetting->estimate_digit - strlen($this->lastEstimate);

            for ($i = 0; $i < $condition; $i++) {
                $this->zero = '0' . $this->zero;
            }
        }

        $this->taxes = Tax::all();
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->template = EstimateTemplate::all();
        $this->units = UnitType::all();

        $this->estimateTemplate = null;
        $this->estimateTemplateItem = null;

        if (request()->has('template')) {
            $this->estimateTemplate = EstimateTemplate::findOrFail(request('template'));

            $this->estimateTemplateItem = EstimateTemplateItem::with('estimateTemplateItemImage')->where('estimate_template_id', request('template'))->get();
        }

        if (request()->has('estimate-request')) {
            $this->estimateTemplate = EstimateRequest::findOrFail(request('estimate-request'));
            $this->estimateRequestId = $this->estimateTemplate->id;
        }


        $estimate = new Estimate();
        $getCustomFieldGroupsWithFields = $estimate->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->client = isset(request()->default_client) ? User::findOrFail(request()->default_client) : null;

        $isClient = User::isClient(user()->id);

        if ($isClient) {
            $this->client = User::findOrFail(user()->id);
        }

        if (request()->has('estimate-request')) {
            $this->client = EstimateRequest::findOrFail(request('estimate-request'))->client;
        }

        $this->view = 'estimates.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('estimates.create', $this->data);

    }

    public function store(StoreEstimate $request)
    {
        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $amount = $request->amount;

        if (trim($items[0]) == '' || trim($cost_per_item[0]) == '') {
            return Reply::error(__('messages.addItem'));
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

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('messages.itemBlank'));
            }
        }

        $estimate = new Estimate();
        $estimate->client_id = $request->client_id;
        $estimate->valid_till = companyToYmd($request->valid_till);
        $estimate->sub_total = round($request->sub_total, 2);
        $estimate->total = round($request->total, 2);
        $estimate->currency_id = $request->currency_id;
        $estimate->note = trim_editor($request->note);
        $estimate->discount = round($request->discount_value, 2);
        $estimate->discount_type = $request->discount_type;
        $estimate->status = 'waiting';
        $estimate->description = trim_editor($request->description);
        $estimate->estimate_number = $request->estimate_number;
        $estimate->estimate_request_id = $request->estimate_request_id ?? null;
        $estimate->save();

        if (request()->has('estimate_request_id')) {
            $estimateRequest = EstimateRequest::findOrFail(request('estimate_request_id'));
            $estimateRequest->update(['status' => 'accepted', 'estimate_id' => $estimate->id]);
        }


        // To add custom fields data
        if ($request->custom_fields_data) {
            $estimate->updateCustomFieldData($request->custom_fields_data);
        }

        $this->logSearchEntry($estimate->id, $estimate->estimate_number, 'estimates.show', 'estimate');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('estimates.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['estimateId' => $estimate->id, 'redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $this->invoice = Estimate::with('sign', 'client', 'unit', 'clientdetails')->findOrFail($id)->withCustomFields();
        $this->viewPermission = user()->permission('view_estimates');

        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->invoice->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->invoice->client_id == user()->id)
            || ($this->viewPermission == 'both' && ($this->invoice->client_id == user()->id || $this->invoice->added_by == user()->id))
        ));

        if($this->invoice->discount_type == 'percent') {
            $discountAmount = $this->invoice->discount;
            $this->discountType = $discountAmount.'%';
        }else {
            $discountAmount = $this->invoice->discount;
            $this->discountType = currency_format($discountAmount, $this->invoice->currency_id);
        }

        $getCustomFieldGroupsWithFields = $this->invoice->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = $this->invoice->estimate_number;

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

        $this->firstEstimate = Estimate::orderBy('id', 'desc')->first();

        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $this->invoice->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

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

        $this->settings = company();
        $this->invoiceSetting = invoice_setting();

        if (in_array('client', user_roles())) {
            $lastViewed = now();
            $ipAddress = request()->ip();
            $this->invoice->last_viewed = $lastViewed;
            $this->invoice->ip_address = $ipAddress;
            $this->invoice->save();
        }

        return view('estimates.show', $this->data);

    }

    public function edit($id)
    {
        $this->estimate = Estimate::with('items.estimateItemImage')->findOrFail($id)->withCustomFields();

        $this->editPermission = user()->permission('edit_estimates');

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->estimate->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->estimate->client_id == user()->id)
            || ($this->editPermission == 'both' && ($this->estimate->client_id == user()->id || $this->estimate->added_by == user()->id))
        ));

        $this->pageTitle = $this->estimate->estimate_number;

        $getCustomFieldGroupsWithFields = $this->estimate->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->units = UnitType::all();
        $this->clients = User::allClients();
        $this->currencies = Currency::all();
        $this->taxes = Tax::all();
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->invoiceSetting = invoice_setting();

        $this->view = 'estimates.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('estimates.create', $this->data);
    }

    public function update(StoreEstimate $request, $id)
    {
        $items = $request->item_name;
        $itemsSummary = $request->item_summary;
        $hsn_sac_code = $request->hsn_sac_code;
        $unitId = request()->unit_id;
        $product = request()->product_id;
        $tax = $request->taxes;
        $quantity = $request->quantity;
        $cost_per_item = $request->cost_per_item;
        $amount = $request->amount;
        $invoice_item_image = $request->invoice_item_image;
        $invoice_item_image_url = $request->invoice_item_image_url;
        $item_ids = $request->item_ids;

        if (trim($items[0]) == '' || trim($items[0]) == '' || trim($cost_per_item[0]) == '') {
            return Reply::error(__('messages.addItem'));
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

        $estimate = Estimate::findOrFail($id);
        $estimate->client_id = $request->client_id;
        $estimate->valid_till = companyToYmd($request->valid_till);
        $estimate->sub_total = round($request->sub_total, 2);
        $estimate->total = round($request->total, 2);
        $estimate->discount = round($request->discount_value, 2);
        $estimate->discount_type = $request->discount_type;
        $estimate->currency_id = $request->currency_id;
        $estimate->status = $request->status;
        $estimate->note = trim_editor($request->note);
        $estimate->description = trim_editor($request->description);
        $estimate->estimate_number = $request->estimate_number;
        $estimate->save();

        /*
            Step1 - Delete all items which are not avaialable
            Step2 - Find old items, update it and check if images are newer or older
            Step3 - Insert new items with images
        */

        if (!empty($request->item_name) && is_array($request->item_name)) {
            // Step1 - Delete all invoice items which are not avaialable
            if (!empty($item_ids)) {
                EstimateItem::whereNotIn('id', $item_ids)->where('estimate_id', $estimate->id)->delete();
            }else {
                // If `item_ids` is empty, delete all items for the estimate
                EstimateItem::where('estimate_id', $estimate->id)->delete();
            }

            // Step2&3 - Find old invoices items, update it and check if images are newer or older
            foreach ($items as $key => $item) {
                $invoice_item_id = isset($item_ids[$key]) ? $item_ids[$key] : 0;

                try {
                    $estimateItem = EstimateItem::findOrFail($invoice_item_id);
                } catch (Exception) {
                    $estimateItem = new EstimateItem();
                }

                $estimateItem->estimate_id = $estimate->id;
                $estimateItem->item_name = $item;
                $estimateItem->item_summary = $itemsSummary[$key];
                $estimateItem->type = 'item';
                $estimateItem->unit_id = (isset($unitId[$key]) && !is_null($unitId[$key])) ? $unitId[$key] : null;
                $estimateItem->product_id = (isset($product[$key]) && !is_null($product[$key])) ? $product[$key] : null;
                $estimateItem->hsn_sac_code = (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null;
                $estimateItem->quantity = $quantity[$key];
                $estimateItem->unit_price = round($cost_per_item[$key], 2);
                $estimateItem->amount = round($amount[$key], 2);
                $estimateItem->taxes = ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null);
                $estimateItem->field_order = $key + 1;
                $estimateItem->save();

                /* Invoice file save here */
                if ((isset($invoice_item_image[$key]) && $request->hasFile('invoice_item_image.' . $key)) || isset($invoice_item_image_url[$key])) {

                    /* Delete previous uploaded file if it not a product (because product images cannot be deleted) */
                    //phpcs:ignore
                    if (!isset($invoice_item_image_url[$key]) && $estimateItem && $estimateItem->estimateItemImage) {
                        Files::deleteFile($estimateItem->estimateItemImage->hashname, EstimateItemImage::FILE_PATH . '/' . $estimateItem->id . '/');
                    }

                    $filename = '';

                    if (isset($invoice_item_image[$key])) {
                        $filename = Files::uploadLocalOrS3($invoice_item_image[$key], EstimateItemImage::FILE_PATH . '/' . $estimateItem->id . '/');
                    }

                    EstimateItemImage::updateOrCreate(
                        [
                            'estimate_item_id' => $estimateItem->id,
                        ],
                        [
                            'filename' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getClientOriginalName() : null,
                            'hashname' => isset($invoice_item_image[$key]) ? $filename : null,
                            'size' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getSize() : null,
                            'external_link' => isset($invoice_item_image[$key]) ? null : (isset($invoice_item_image_url[$key]) ? $invoice_item_image_url[$key] : null),
                        ]
                    );
                }
            }
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $estimate->updateCustomFieldData($request->custom_fields_data);
        }

        return Reply::redirect(route('estimates.index'), __('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $estimate = Estimate::findOrFail($id);

        $this->deletePermission = user()->permission('delete_estimates');

        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $estimate->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $estimate->client_id == user()->id)
            || ($this->deletePermission == 'both' && ($estimate->client_id == user()->id || $estimate->added_by == user()->id))
        ));

        Estimate::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));

    }

    public function download($id)
    {
        $this->invoiceSetting = invoice_setting();
        $this->estimate = Estimate::with('unit')->findOrFail($id)->withCustomFields();

        $getCustomFieldGroupsWithFields = $this->estimate->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->viewPermission = user()->permission('view_estimates');

        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->estimate->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->estimate->client_id == user()->id)
            || ($this->viewPermission == 'both' && ($this->estimate->client_id == user()->id || $this->estimate->added_by == user()->id))
        ));

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->invoiceSetting = invoice_setting();
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        $this->estimate = Estimate::findOrFail($id)->withCustomFields();

        $getCustomFieldGroupsWithFields = $this->estimate->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->discount = 0;

        if ($this->estimate->discount > 0) {

            if ($this->estimate->discount_type == 'percent') {
                $this->discount = (($this->estimate->discount / 100) * $this->estimate->sub_total);
            }
            else {
                $this->discount = $this->estimate->discount;
            }
        }

        $taxList = array();

        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $this->estimate->id)
            ->get();
        $this->invoiceSetting = invoice_setting();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($this->estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {
                        if ($this->estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->settings = $this->estimate->company;

        $this->invoiceSetting = invoice_setting();

        $pdf = app('dompdf.wrapper');

        $pdf->setOption('enable_php', true);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        // $pdf->loadView('estimates.pdf.' . $this->invoiceSetting->template, $this->data);
        $customCss = '<style>
                * { text-transform: none !important; }
            </style>';

        $pdf->loadHTML($customCss . view('estimates.pdf.' . $this->invoiceSetting->template, $this->data)->render());

        $filename = $this->estimate->estimate_number;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function sendEstimate($id)
    {
        $estimate = Estimate::findOrFail($id);

        $estimate->send_status = 1;

        if ($estimate->status == 'draft') {
            $estimate->status = 'waiting';
        }

        $estimate->save();
        event(new NewEstimateEvent($estimate));

        return Reply::success(__('messages.updateSuccess'));
    }

    public function changeStatus(Request $request, $id)
    {
        $estimate = Estimate::findOrFail($id);
        $estimate->status = 'canceled';
        $estimate->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function acceptModal(Request $request, $id)
    {
        return view('estimates.ajax.accept-estimate', ['id' => $id]);
    }

    public function accept(EstimateAcceptRequest $request, $id)
    {
        DB::beginTransaction();

        $estimate = Estimate::with('sign')->findOrFail($id);

        /** @phpstan-ignore-next-line */
        if ($estimate && $estimate->sign) {
            return Reply::error(__('messages.alreadySigned'));
        }

        $accept = new AcceptEstimate();
        $accept->full_name = $request->first_name . ' ' . $request->last_name;
        $accept->estimate_id = $estimate->id;
        $accept->email = $request->email;
        $imageName = null;

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            Files::createDirectoryIfNotExist('estimate/accept');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/estimate/accept/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'estimate/accept', $estimate->company_id);

        }
        else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'estimate/accept/', 300);
            }
        }

        $accept->signature = $imageName;
        $accept->save();

        $estimate->status = 'accepted';
        $estimate->save();

        $invoiceExist = Invoice::where('estimate_id', $estimate->id)->first();

        if (is_null($invoiceExist)) {

            $invoice = new Invoice();

            $invoice->client_id = $estimate->client_id;
            $invoice->issue_date = now($this->company->timezone)->format('Y-m-d');
            $invoice->due_date = now($this->company->timezone)->addDays(invoice_setting()->due_after)->format('Y-m-d');
            $invoice->sub_total = round($estimate->sub_total, 2);
            $invoice->discount = round($estimate->discount, 2);
            $invoice->discount_type = $estimate->discount_type;
            $invoice->total = round($estimate->total, 2);
            $invoice->currency_id = $estimate->currency_id;
            $invoice->note = trim_editor($estimate->note);
            $invoice->status = 'unpaid';
            $invoice->estimate_id = $estimate->id;
            $invoice->invoice_number = Invoice::lastInvoiceNumber() + 1;
            $invoice->save();

            /** @phpstan-ignore-next-line */
            foreach ($estimate->items as $item) :
                if (!is_null($item)) {
                    InvoiceItems::create(
                        [
                            'invoice_id' => $invoice->id,
                            'item_name' => $item->item_name,
                            'item_summary' => $item->item_summary ?: '',
                            'type' => 'item',
                            'quantity' => $item->quantity,
                            'unit_price' => round($item->unit_price, 2),
                            'amount' => round($item->amount, 2),
                            'taxes' => $item->taxes
                        ]
                    );
                }

            endforeach;

            // Log search
            $this->logSearchEntry($invoice->id, $invoice->invoice_number, 'invoices.show', 'invoice');
        }


        DB::commit();

        return Reply::redirect(route('estimates.index'), __('messages.estimateSigned'));
    }

    public function decline(Request $request, $id)
    {
        $estimate = Estimate::findOrFail($id);
        $estimate->status = 'declined';
        $estimate->save();

        return Reply::dataOnly(['status' => 'success']);
    }

    public function deleteEstimateItemImage(Request $request)
    {
        $item = EstimateItemImage::where('estimate_item_id', $request->invoice_item_id)->first();

        if ($item) {
            Files::deleteFile($item->hashname, 'estimate-files/' . $item->id . '/');
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
