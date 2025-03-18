<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Estimate;
use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\ProductCategory;
use App\Models\EstimateTemplate;
use Illuminate\Support\Facades\App;
use App\Models\EstimateTemplateItem;
use App\Models\EstimateTemplateItemImage;
use App\DataTables\EstimateTemplateDataTable;
use App\Http\Requests\EstimateTemplate\StoreRequest;

class EstimateTemplateController extends AccountBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.estimates.estimateTemplate';

    }

    public function index(EstimateTemplateDataTable $dataTable)
    {
        $this->addPermission = user()->permission('add_estimates');
        return $dataTable->render('estimates-templates.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('modules.estimates.createestimateTemplate');

        $this->taxes = Tax::all();

        $this->currencies = Currency::all();
        $this->units = UnitType::all();
        $this->invoiceSetting = invoice_setting();

        $this->products = Product::all();
        $this->categories = ProductCategory::all();

        $this->view = 'estimates-templates.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('estimates.create', $this->data);
    }

    public function store(StoreRequest $request)
    {
        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $amount = $request->amount;

        if (isset($items[0]) && (trim($items[0]) == '' || trim($items[0]) == '' || isset($cost_per_item[0]) && trim($cost_per_item[0]) == '')) {
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

        $estimate = new EstimateTemplate();
        $estimate->name = $request->name;
        $estimate->sub_total = $request->sub_total;
        $estimate->total = $request->total;
        $estimate->currency_id = $request->currency_id;
        $estimate->discount = round($request->discount_value, 2);
        $estimate->discount_type = $request->discount_type;
        $estimate->signature_approval = ($request->require_signature) ? 1 : 0;
        $estimate->description = trim_editor($request->description);
        $estimate->added_by = user()->id;
        $estimate->save();


        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('estimate-template.index');
        }

        $this->logSearchEntry($estimate->id, 'Estimate #' . $estimate->id, 'estimates.show', 'estimate');

        return Reply::redirect($redirectUrl, __('messages.estimateTemplateCreated'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->invoice = EstimateTemplate::with('items', 'clients', 'items.estimateTemplateItemImage', 'units')->findOrFail($id);

        $this->pageTitle = __('modules.lead.estimateTemplate') . '#' . $this->invoice->id;

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


        $items = EstimateTemplateItem::whereNotNull('taxes')
            ->where('estimate_template_id', $this->invoice->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateTemplateItem::taxbyid($tax)->first();

                if($this->tax){
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])){
                        /** @phpstan-ignore-next-line */
                        if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        } else{
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {
                        /** @phpstan-ignore-next-line */
                        if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }

            }
        }

        $this->taxes = $taxList;

        $this->settings = global_setting();
        $this->invoiceSetting = invoice_setting();

        return view('estimates-templates.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->pageTitle = __('modules.estimates.updateEstimateTemplate');

        $this->estimate = EstimateTemplate::with('items', 'clients')->findOrFail($id);
        $this->editPermission = user()->permission('edit_estimates');

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->estimate->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->estimate->added_by != user()->id) || $this->editPermission == 'both'
        ));

        $this->taxes = Tax::all();
        $this->currencies = Currency::all();
        $this->units = UnitType::all();
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->invoiceSetting = invoice_setting();

        $this->view = 'estimates-templates.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('estimates-templates.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $amount = $request->amount;

        if (isset($items[0]) && (trim($items[0]) == '' || trim($items[0]) == '' || isset($cost_per_item[0]) && trim($cost_per_item[0]) == '')) {
            return Reply::error(__('messages.addItem'));
        }

        foreach ($quantity as $qty) {
            if (!is_numeric($qty)) {
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

        $estimateTemplate = EstimateTemplate::findOrFail($id);
        $estimateTemplate->name = $request->name;
        $estimateTemplate->sub_total = $request->sub_total;
        $estimateTemplate->total = $request->total;
        $estimateTemplate->currency_id = $request->currency_id;
        $estimateTemplate->discount = round($request->discount_value, 2);
        $estimateTemplate->discount_type = $request->discount_type;
        $estimateTemplate->signature_approval = ($request->require_signature) ? 1 : 0;
        $estimateTemplate->description = trim_editor($request->description);
        $estimateTemplate->save();

        return Reply::redirect(route('estimate-template.index', $estimateTemplate->id), __('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_estimates');
        $estimate = EstimateTemplate::findOrFail($id);

        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $estimate->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $estimate->added_by != user()->id) || $this->deletePermission == 'both'
        ));

        EstimateTemplate::destroy($id);

        return Reply::success(__('messages.estimateTemplateDeleted'));
    }

    public function deleteEstimateItemImage(Request $request)
    {
        $item = EstimateTemplateItemImage::where('estimate_template_item_id', $request->invoice_item_id)->first();

        if ($item) {
            Files::deleteFile($item->hashname, 'estimate-files/' . $item->id . '/');
            $item->delete();
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function domPdfObjectForDownload($id)
    {
        $this->invoiceSetting = invoice_setting();
        $this->estimateTemplate = EstimateTemplate::with('items', 'clients', 'currency', 'units')->findOrFail($id);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        if ($this->estimateTemplate->discount > 0) {
            if ($this->estimateTemplate->discount_type == 'percent') {
                $this->discount = (($this->estimateTemplate->discount / 100) * $this->estimateTemplate->sub_total);
            }
            else {
                $this->discount = $this->estimateTemplate->discount;
            }
        }
        else {
            $this->discount = 0;
        }

        $taxList = array();

        $items = EstimateTemplateItem::whereNotNull('taxes')
            ->where('estimate_template_id', $this->estimateTemplate->id)
            ->get();
        $this->invoiceSetting = invoice_setting();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateTemplateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        /** @phpstan-ignore-next-line */
                        if ($this->estimateTemplate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->estimateTemplate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        } else{
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->settings = global_setting();

        $pdf = app('dompdf.wrapper');


        $pdf->setOption('enable_php', true);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);


        $pdf->loadView('estimates-templates.pdf.' . $this->invoiceSetting->template, $this->data);

        $filename = __('modules.estimates.estimateTemplate') . '-' . $this->estimateTemplate->id;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
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

}
