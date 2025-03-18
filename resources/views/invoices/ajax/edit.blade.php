@php
    $addProductPermission = user()->permission('add_product');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<!-- for sortable content -->
<link rel="stylesheet" href="{{ asset('vendor/css/jquery-ui.css') }}">

@if (!in_array('clients', user_modules()))
    <x-alert class="mb-3" type="danger" icon="exclamation-circle"><span>@lang('messages.enableClientModule')</span>
        <x-forms.link-secondary icon="arrow-left" :link="route('invoices.index')">@lang('app.back')</x-forms.link-secondary>
    </x-alert>
@else

    <!-- CREATE INVOICE START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-lg-4 px-md-4 px-3 py-3">
        <h4 class="mb-0 f-21 font-weight-normal ">@lang('app.invoiceDetails')</h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="saveInvoiceForm">
        @method('PUT')
        <!-- INVOICE NUMBER, DATE, DUE DATE, FREQUENCY START -->
                <input type="hidden" name="do_it_later" id="doItLater" value="direct">
        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <!-- INVOICE NUMBER START -->
            <div class="col-md-2">
                <div class="form-group mb-lg-0 mb-md-0 mb-4">
                    <x-forms.label class="mb-12" fieldId="invoice_number"
                                   :fieldLabel="__('modules.invoices.invoiceNumber')" fieldRequired="true">
                    </x-forms.label>
                    <x-forms.input-group>
                        <input type="text" name="invoice_number" id="invoice_number" class="form-control height-35 f-15 readonly-background" readonly
                               value="{{ $invoice->invoice_number }}">
                    </x-forms.input-group>
                </div>
            </div>
            <!-- INVOICE NUMBER END -->
            <!-- INVOICE DATE START -->
            <div class="col-md-2">
                <div class="form-group mb-lg-0 mb-md-0 mb-4">
                    <x-forms.label fieldId="due_date" :fieldLabel="__('modules.invoices.invoiceDate')">
                    </x-forms.label>
                    <div class="input-group">
                        <input type="text" id="invoice_date" name="issue_date"
                               class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                               placeholder="@lang('placeholders.date')"
                               value="{{ $invoice->issue_date->translatedFormat(company()->date_format) }}">
                    </div>
                </div>
            </div>
            <!-- INVOICE DATE END -->
            <!-- DUE DATE START -->
            <div class="col-md-2">
                <div class="form-group mb-lg-0 mb-md-0 mb-4">
                    <x-forms.label fieldId="due_date" :fieldLabel="__('app.dueDate')"></x-forms.label>
                    <div class="input-group ">
                        <input type="text" id="due_date" name="due_date"
                               class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                               placeholder="@lang('placeholders.date')"
                               value="{{ $invoice->due_date->translatedFormat(company()->date_format) }}">
                    </div>
                </div>
            </div>
            <!-- DUE DATE END -->
            <!-- FREQUENCY START -->
            <div class="col-md-3">
                <div class="form-group c-inv-select mb-lg-0 mb-md-0 mb-4">
                    <x-forms.label fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')">
                    </x-forms.label>

                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="currency_id" id="currency_id">
                            @foreach ($currencies as $currency)
                                <option @if ($invoice->currency_id == $currency->id) selected @endif value="{{ $currency->id }}"
                                        data-currency-code="{{$currency->currency_code}}">
                                    {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <!-- FREQUENCY END -->
            <div class="col-md-3">
                <x-forms.label fieldId="exchange_rate" :fieldLabel="__('modules.currencySettings.exchangeRate')" fieldRequired="true">
                </x-forms.label>
                <input type="number" id="exchange_rate" name="exchange_rate"
                       class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15" value="{{$invoice->exchange_rate}}" @if($companyCurrency->id == $invoice->currency_id) readonly @endif>
                <small id="currency_exchange" class="form-text text-muted">@if (company()->currency->currency_code != $invoice->currency->currency_code) ( {{$invoice->currency->currency_code}} @lang('app.to') {{company()->currency->currency_code}} ) @endif </small>
            </div>
        </div>
        <!-- INVOICE NUMBER, DATE, DUE DATE, FREQUENCY END -->

        <hr class="m-0 border-top-grey">

        <!-- CLIENT, PROJECT, GST, BILLING, SHIPPING ADDRESS START -->
        <div class="row px-lg-4 px-md-4 px-3 pt-3">

            <!-- CLIENT START -->
            <div class="col-md-4">
                <x-client-selection-dropdown :clients="$clients" :selected="$invoice->client_id" />
            </div>
            <!-- CLIENT END -->
            <!-- PROJECT START -->
            <div class="col-md-4">
                <x-forms.label fieldId="project_id" :fieldLabel="__('app.project')">
                </x-forms.label>
                <div class="form-group c-inv-select mb-4">
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" data-live-search="true" data-size="8"
                                name="project_id" id="project_id">
                            <option value="">--</option>
                            @if($invoice?->client?->projects)
                                @foreach ($invoice->client->projects as $item)
                                    <option @if ($invoice->project_id == $item->id) selected @endif value="{{ $item->id }}">
                                        {{ $item->project_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <!-- PROJECT END -->

            <div class="col-md-4">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="calculate_tax" :fieldLabel="__('modules.invoices.calculateTax')">
                    </x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" data-live-search="true" data-size="8"
                                name="calculate_tax" id="calculate_tax">
                            <option value="after_discount">@lang('modules.invoices.afterDiscount')</option>
                            <option value="before_discount" @if ($invoice->calculate_tax == 'before_discount') selected @endif>@lang('modules.invoices.beforeDiscount')</option>
                        </select>
                    </div>
                </div>
            </div>

            @if($linkInvoicePermission == 'all')
                <div class="col-md-4">
                    <div class="form-group c-inv-select my-4">
                        <x-forms.label fieldId="bank_account_id" :fieldLabel="__('app.menu.bankaccount')">
                        </x-forms.label>
                        <div class="select-others height-35 rounded">
                            <select class="form-control select-picker" data-live-search="true" data-size="8"
                                name="bank_account_id" id="bank_account_id">
                                <option value="">--</option>
                                @if($viewBankAccountPermission != 'none')
                                    @foreach ($bankDetails as $bankDetail)
                                        <option value="{{ $bankDetail->id }}" @if($bankDetail->id == $invoice->bank_account_id) selected @endif>@if($bankDetail->type == 'bank')
                                                {{ $bankDetail->bank_name }} | @endif
                                            {{ $bankDetail->account_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-md-4">
                <div class="form-group c-inv-select my-4">
                    <x-forms.label fieldId="invoice_payment_id" :fieldLabel="__('modules.invoices.paymentDetails')">
                    </x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" data-live-search="true" data-size="8"
                            name="invoice_payment_id" id="invoice_payment_id">
                            <option value="">--</option>
                            @foreach ($invoicePayments as $invoicePayment)
                            <option value="{{ $invoicePayment->id }}"  @if($invoicePayment->id == $invoice->invoice_payment_id) selected @endif>
                                {{ $invoicePayment->title }}
                            </option>
                        @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if ($invoice->amountPaid() == 0 && $invoice->status == 'paid')
                <!-- STATUS START -->
                <div class="col-md-4">
                    <x-forms.label fieldId="status" :fieldLabel="__('app.status')">
                    </x-forms.label>
                    <div class="form-group c-inv-select mb-4">
                        <div class="select-others height-35 rounded">
                            <select class="form-control select-picker" name="status" id="status">
                                <option @if ($invoice->status == 'unpaid') selected @endif value="unpaid">@lang('modules.invoices.unpaid')
                                </option>
                                <option @if ($invoice->status == 'paid') selected @endif value="paid">@lang('modules.invoices.paid')
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- STATUS END -->
            @endif

        </div>

        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <!-- BILLING ADDRESS START -->
            <div class="col-md-4">
                <div class="form-group c-inv-select mb-0">
                    <label class="f-14 text-dark-grey mb-12  w-100"
                           for="usr">@lang('modules.invoices.billingAddress')</label>
                    <p class="f-15" id="client_billing_address">{!! nl2br($invoice->client?->clientDetails?->address) !!}</p>
                </div>
            </div>
            <!-- BILLING ADDRESS END -->
            <!-- SHIPPING ADDRESS START -->
            <div class="col-md-4">
                <div class="form-group c-inv-select mb-lg-0 mb-md-0 mb-4">
                    <label class="f-14 text-dark-grey mb-12  w-100"
                           for="usr">@lang('modules.invoices.shippingAddress')</label>
                    <p class="f-15" id="client_shipping_address">
                        @if ($invoice->client?->clientDetails?->shipping_address)
                            {!! nl2br($invoice?->client?->clientDetails?->shipping_address) !!}
                        @else
                            <a href="javascript:;" class="" id="show-shipping-field"><i
                                    class="f-12 mr-2 fa fa-plus"></i>@lang('app.addShippingAddress')</a>
                        @endif
                    </p>
                    <p class="d-none" id="add-shipping-field">
                        <textarea class="form-control f-14 pt-2" rows="3" placeholder="@lang('placeholders.address')"
                                  name="shipping_address" id="shipping_address"></textarea>
                    </p>
                </div>
            </div>
            <!-- SHIPPING ADDRESS END -->

            <div class="col-md-4">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="company_address_id" :fieldLabel="__('modules.invoices.generatedBy')">
                    </x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" data-live-search="true" data-size="8"
                                name="company_address_id" id="company_address_id">
                            @foreach ($companyAddresses as $item)
                                <option {{ ($item->id == $invoice->company_address_id) ? 'selected' : '' }} value="{{ $item->id }}">
                                    {{ $item->location }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- CLIENT, PROJECT, GST, BILLING, SHIPPING ADDRESS END -->

        <x-forms.custom-field :fields="$fields" :model="$invoice"></x-forms.custom-field>

        <hr class="m-0 border-top-grey">

        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <div class="col-md-3 d-none product-category-filter">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.input-group>
                        <select class="form-control select-picker" name="category_id"
                                id="product_category_id" data-live-search="true">
                            <option value="null">{{ __('app.select') . ' ' . __('app.product') . ' ' . __('app.category')  }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </x-forms.input-group>
                </div>
            </div>
            @if(in_array('products', user_modules()) || in_array('purchase', user_modules()))
                <div class="col-md-3">
                    <div class="form-group c-inv-select mb-4">
                    <x-forms.input-group>
                        <select class="form-control select-picker" data-live-search="true" data-size="8" id="add-products" title="{{ __('app.menu.selectProduct') }}">
                        @if (in_array('purchase', user_modules()))
                            @foreach ($products as $item)
                                @if ((!empty($item->inventory) && count($item->inventory) > 0 && $item->inventory[0]) || ($item->type == 'service'))
                                    @if (($item->track_inventory == 1 && $item->inventory[0]->net_quantity > 0) || ($item->type == 'service'))
                                        <option data-content="{{ $item->name }}" value="{{ $item->id }}">
                                            {{ $item->name }}</option>
                                    @endif
                                @endif
                            @endforeach
                        @else
                            @foreach ($products as $item)
                                <option data-content="{{ $item->name }}" value="{{ $item->id }}">
                                    {{ $item->name }}</option>
                            @endforeach
                        @endif
                        </select>
                        <x-slot name="preappend">
                            <a href="javascript:;"
                                class="btn btn-outline-secondary border-grey toggle-product-category"
                                data-toggle="tooltip" data-original-title="{{ __('modules.productCategory.filterByCategory') }}"><i class="fa fa-filter"></i></a>
                        </x-slot>
                        @if ($addProductPermission == 'all' || $addProductPermission == 'added')
                            @if (module_enabled('Purchase'))
                                <x-slot name="append">
                                    <a href="{{ route('purchase-products.create') }}" data-redirect-url="no"
                                        class="btn btn-outline-secondary border-grey openRightModal"
                                        data-toggle="tooltip"
                                        data-original-title="{{ __('app.add') . ' ' . __('modules.dashboard.newproduct') }}">@lang('app.add')</a>
                                </x-slot>
                            @else
                                <x-slot name="append">
                                    <a href="{{ route('products.create') }}" data-redirect-url="no"
                                        class="btn btn-outline-secondary border-grey openRightModal"
                                        data-toggle="tooltip"
                                        data-original-title="{{ __('app.add') . ' ' . __('modules.dashboard.newproduct') }}">@lang('app.add')</a>
                                </x-slot>
                            @endif
                        @endif
                    </x-forms.input-group>
                    </div>
                </div>
            @endif

        </div>

        <div id="sortable">
            @foreach ($invoice->items->sortBy('field_order') as $key => $item)
                <!-- DESKTOP DESCRIPTION TABLE START -->
                <div class="d-flex px-4 py-3 c-inv-desc item-row">
                    <div class="d-flex align-items-center">
                        <span class="ui-icon ui-icon-arrowthick-2-n-s mr-2"></span>
                        <input type="hidden" name="sort_order[]"
                               value="{{ $item->id }}">
                    </div>

                    <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                        <table width="100%">
                            <tbody>
                            <tr class="text-dark-grey font-weight-bold f-14">
                                <td width="{{ $invoiceSetting->hsn_sac_code_show ? '40%' : '50%' }}"
                                    class="border-0 inv-desc-mbl btlr">@lang('app.description')
                                    <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                </td>
                                @if ($invoiceSetting->hsn_sac_code_show)
                                    <td width="10%" class="border-0" align="right">@lang("app.hsnSac")</td>
                                @endif
                                <td width="10%" class="border-0" align="right">@lang('modules.invoices.qty')</td>
                                <td width="10%" class="border-0" align="right">
                                    @lang("modules.invoices.unitPrice")</td>
                                <td width="13%" class="border-0" align="right">@lang('modules.invoices.tax')
                                </td>
                                <td width="17%" class="border-0 bblr-mbl" align="right">
                                    @lang('modules.invoices.amount')</td>
                            </tr>
                            <tr>
                                <td class="border-bottom-0 btrr-mbl btlr">
                                    <input type="text" class="form-control f-14 border-0 w-100 item_name"
                                           name="item_name[]" placeholder="@lang('modules.expenses.itemName')"
                                           value="{{ $item->item_name }}">
                                </td>
                                <td class="border-bottom-0 d-block d-lg-none d-md-none">
                                        <textarea class="f-14 border-0 w-100 mobile-description form-control"
                                                  placeholder="@lang('placeholders.invoices.description')"
                                                  name="item_summary[]">{{ $item->item_summary }}</textarea>
                                </td>
                                @if ($invoiceSetting->hsn_sac_code_show)
                                    <td class="border-bottom-0">
                                        <input type="text" class="f-14 border-0 w-100 text-right hsn_sac_code form-control"
                                               value="{{ $item->hsn_sac_code }}" name="hsn_sac_code[]">
                                    </td>
                                @endif
                                <td class="border-bottom-0">
                                    <input type="number" min="1"
                                           class="form-control f-14 border-0 w-100 text-right quantity mt-3"
                                           value="{{ $item->quantity }}" name="quantity[]">

                                    @if (!is_null($item->product_id) && $item->product_id != 0)
                                        <span class="text-dark-grey float-right border-0 f-12">{{ $item->unit->unit_type }}</span>
                                        <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                        <input type="hidden" name="unit_id[]" value="{{ $item->unit_id }}">
                                    @else
                                        <select class="text-dark-grey float-right border-0 f-12" name="unit_id[]">
                                            @foreach ($units as $unit)
                                                <option
                                                @if ($item->unit_id == $unit->id) selected @endif
                                                value="{{ $unit->id }}">{{ $unit->unit_type }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="product_id[]" value="">
                                    @endif
                                </td>
                                <td class="border-bottom-0">
                                    <input type="number" min="1"
                                           class="f-14 border-0 w-100 text-right cost_per_item form-control" placeholder="0.00"
                                           value="{{ $item->unit_price }}" name="cost_per_item[]">
                                </td>
                                <td class="border-bottom-0">
                                    <div class="select-others height-35 rounded border-0">
                                        <select id="multiselect{{ $key }}"
                                                name="taxes[{{ $key }}][]" multiple="multiple"
                                                class="select-picker type customSequence border-0" data-size="3">
                                            @foreach ($taxes as $tax)
                                                <option data-rate="{{ $tax->rate_percent }}" data-tax-text="{{ $tax->tax_name .':'. $tax->rate_percent }}%"
                                                        @if (isset($item->taxes) && array_search($tax->id, json_decode($item->taxes)) !== false) selected @endif value="{{ $tax->id }}">
                                                    {{ $tax->tax_name }}:
                                                    {{ $tax->rate_percent }}%</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td rowspan="2" align="right" valign="top" class="bg-amt-grey btrr-bbrr">
                                        <span
                                            class="amount-html">{{ number_format((float) $item->amount, 2, '.', '') }}</span>
                                    <input type="hidden" class="amount" name="amount[]"
                                           value="{{ $item->amount }}">
                                </td>
                            </tr>
                            <tr class="d-none d-md-block d-lg-table-row">
                                <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '4' : '3' }}"
                                    class="dash-border-top bblr">
                                        <textarea class="f-14 border-0 w-100 desktop-description form-control" name="item_summary[]"
                                                  placeholder="@lang('placeholders.invoices.description')">{{ $item->item_summary }}</textarea>
                                </td>
                                <td class="border-left-0">
                                    <input type="file"
                                           class="dropify"
                                           name="invoice_item_image[]"
                                           data-allowed-file-extensions="png jpg jpeg bmp"
                                           data-messages-default="test"
                                           data-height="70"
                                           data-id="{{ $item->id }}"
                                           id="{{ $item->id }}"
                                           data-default-file="{{ $item->invoiceItemImage ? $item->invoiceItemImage->file_url : '' }}"
                                           @if ($item->invoiceItemImage && $item->invoiceItemImage->external_link)
                                               data-show-remove="false"
                                        @endif
                                    />
                                    <input type="hidden" name="invoice_item_image_url[]" value="{{ $item->invoiceItemImage ? $item->invoiceItemImage->file : '' }}">
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <a href="javascript:;"
                           class="d-flex align-items-center justify-content-center ml-3 remove-item"><i
                                class="fa fa-times-circle f-20 text-lightest"></i></a>
                    </div>
                </div>
                <!-- DESKTOP DESCRIPTION TABLE END -->
            @endforeach
        </div>
        <!--  ADD ITEM START-->
        <div class="row px-lg-4 px-md-4 px-3 pb-3 pt-0 mb-3  mt-2">
            <div class="col-md-12">
                <a class="f-15 f-w-500" href="javascript:;" id="add-item"><i
                        class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.invoices.addItem')</a>
            </div>
        </div>
        <!--  ADD ITEM END-->

        <hr class="m-0 border-top-grey">

        <!-- TOTAL, DISCOUNT START -->
        <div class="d-flex px-lg-4 px-md-4 px-3 pb-3 c-inv-total">
            <table width="100%" class="text-right f-14 ">
                <tbody>
                <tr>
                    <td width="50%" class="border-0 d-lg-table d-md-table d-none"></td>
                    <td width="50%" class="p-0 border-0 c-inv-total-right">
                        <table width="100%">
                            <tbody>
                            <tr>
                                <td colspan="2" class="border-top-0 text-dark-grey">
                                    @lang('modules.invoices.subTotal')</td>
                                <td width="30%" class="border-top-0 sub-total">
                                    {{ number_format((float) $invoice->sub_total, 2, '.', '') }}</td>
                                <input type="hidden" class="sub-total-field" name="sub_total"
                                       value="{{ $invoice->sub_total }}">
                            </tr>
                            <tr>
                                <td width="20%" class="text-dark-grey">@lang('modules.invoices.discount')
                                </td>
                                <td width="40%" style="padding: 5px;">
                                    <table width="100%">
                                        <tbody>
                                        <tr>
                                            <td width="70%" class="c-inv-sub-padding">
                                                <input type="number" min="0" name="discount_value"
                                                       class="form-control f-14 border-0 w-100 text-right discount_value"
                                                       placeholder="0" value="{{ $invoice->discount }}">
                                            </td>
                                            <td width="30%" align="left" class="c-inv-sub-padding">
                                                <div
                                                    class="select-others select-tax height-35 rounded border-0">
                                                    <select class="form-control select-picker"
                                                            id="discount_type" name="discount_type">
                                                        <option @if ($invoice->discount_type == 'percent') selected @endif value="percent">%
                                                        </option>
                                                        <option @if ($invoice->discount_type == 'fixed') selected @endif value="fixed">
                                                            @lang('modules.invoices.amount')</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td><span
                                        id="discount_amount">{{ number_format((float) $invoice->discount, 2, '.', '') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('modules.invoices.tax')</td>
                                <td colspan="2" class="p-0">
                                    <table width="100%" id="invoice-taxes">
                                        <tr>
                                            <td colspan="2"><span class="tax-percent">0.00</span></td>
                                        </tr>
                                    </table>
                                </td>

                            </tr>
                            <tr class="bg-amt-grey f-16 f-w-500">
                                <td colspan="2">@lang('modules.invoices.total')</td>
                                <td><span
                                        class="total">{{ number_format((float) $invoice->total, 2, '.', '') }}</span>
                                </td>
                                <input type="hidden" class="total-field" name="total"
                                       value="{{ round($invoice->total, 2) }}">
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- TOTAL, DISCOUNT END -->

        <!-- NOTE AND TERMS AND CONDITIONS START -->
        <div class="d-flex flex-wrap px-lg-4 px-md-4 px-3 py-3">
            <div class="col-md-6 col-sm-12 c-inv-note-terms p-0 mb-lg-0 mb-md-0 mb-3">
                <label class="f-14 text-dark-grey mb-12  w-100"
                       for="usr">@lang('modules.invoices.note')</label>
                <textarea class="form-control" name="note" id="note" rows="4"
                          placeholder="@lang('placeholders.invoices.note')">{{ $invoice->note }}</textarea>
            </div>
            <div class="col-md-6 col-sm-12 p-0 c-inv-note-terms">
                <x-forms.label fieldId="" :fieldLabel="__('modules.invoiceSettings.invoiceTerms')">
                </x-forms.label>
                <p>
                    {!! nl2br($invoiceSetting->invoice_terms) !!}
                </p>
            </div>
        </div>
        <!-- NOTE AND TERMS AND CONDITIONS END -->

        <!-- UPLOAD MULTIPLE FILES START -->
        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <!-- INVOICE NUMBER START -->
            <div class="col-md-12">
                <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.menu.addFile')" fieldName="file" fieldId="file-upload-dropzone"/>
            </div>
            <input type="hidden" name="invoiceID" id="invoiceID">
        </div>
        <!-- UPLOAD MULTIPLE FILES END -->

        <div class="d-flex px-lg-4 px-md-4 px-3 py-2 bg-light-grey">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="f-14 text-dark-grey mb-12 w-100" for="payment_status"></label>
                    <div class="d-flex">
                        <x-forms.checkbox fieldId="payment_status" :fieldLabel="__('modules.invoices.receivedPayment')" :fieldValue="$invoice->payment_status" fieldName="payment_status" :checked="$invoice->payment_status == '1'"></x-forms.checkbox>
                    </div>
                </div>
            </div>

            <div class="col-md-3 payment-types @if($invoice->payment_status != 1) d-none @endif">
                <x-forms.select fieldId="payment_gateway_id" :fieldLabel="__('modules.payments.paymentGateway')" fieldName="gateway"
                search="true" fieldRequired="true">
                    <option value="">--</option>
                    <option value="Offline"  id="offline_method" @if($invoice->gateway == 'Offline') selected @endif>{{ __('modules.offlinePayment.offlinePayment') }}</option>
                    @if ($paymentGateway->paypal_status == 'active')
                        <option value="paypal" @if($invoice->gateway == 'paypal') selected @endif>{{ __('app.paypal') }}</option>
                    @endif
                    @if ($paymentGateway->stripe_status == 'active')
                        <option value="stripe" @if($invoice->gateway == 'stripe') selected @endif>{{ __('app.stripe') }}</option>
                    @endif
                    @if ($paymentGateway->razorpay_status == 'active')
                        <option value="razorpay" @if($invoice->gateway == 'razorpay') selected @endif>{{ __('app.razorpay') }}</option>
                    @endif
                    @if ($paymentGateway->paystack_status == 'active')
                        <option value="paystack" @if($invoice->gateway == 'paystack') selected @endif>{{ __('app.paystack') }}</option>
                    @endif
                    @if ($paymentGateway->mollie_status == 'active')
                        <option value="mollie" @if($invoice->gateway == 'mollie') selected @endif>{{ __('app.mollie') }}</option>
                    @endif
                    @if ($paymentGateway->payfast_status == 'active')
                        <option value="payfast" @if($invoice->gateway == 'payfast') selected @endif>{{ __('app.payfast') }}</option>
                    @endif
                    @if ($paymentGateway->authorize_status == 'active')
                        <option value="authorize" @if($invoice->gateway == 'authorize') selected @endif>{{ __('app.authorize') }}</option>
                    @endif
                    @if ($paymentGateway->square_status == 'active')
                        <option value="square" @if($invoice->gateway == 'square') selected @endif>{{ __('app.square') }}</option>
                    @endif
                    @if ($paymentGateway->flutterwave_status == 'active')
                        <option value="flutterwave" @if($invoice->gateway == 'flutterwave') selected @endif>{{ __('app.flutterwave') }}</option>
                    @endif
                </x-forms.select>
            </div>

            <div class="col-md-3 @if($invoice->offline_methods == null || $invoice->payment_status != 1) d-none @endif " id="add_offline">
                <x-forms.select fieldId="offline_method_id" :fieldLabel="__('modules.payments.offlinePaymentMethod')" fieldName="offline_methods"
                search="true" fieldRequired="true">
                    @foreach ($methods as $method)
                        @if($method->status == 'yes')
                        <option @if($invoice->offline_method_id == $method->id) selected @endif value={{$method->id}}>{{$method->name}}</option>
                        @endif
                    @endforeach
                </x-forms.select>
            </div>

            <div class="col-md-3 payment-types @if($invoice->payment_status != 1) d-none @endif">
                <x-forms.text fieldId="transaction_id" :fieldLabel="__('modules.payments.transactionId')"
                    fieldName="transaction_id" :fieldPlaceholder="__('placeholders.payments.transactionId')" :fieldValue="$invoice->transaction_id"/>
            </div>
        </div>

        <!-- CANCEL SAVE SEND START -->
        <x-form-actions class="c-inv-btns">

            <div class="d-flex">

                <div class="inv-action dropup mr-3">
                    <button class="btn-primary dropdown-toggle" type="button" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                        @lang('app.save')
                        <span><i class="fa fa-chevron-up f-15 text-white"></i></span>
                    </button>
                    <!-- DROPDOWN - INFORMATION -->
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuBtn" tabindex="0">
                        <li>
                            <a class="dropdown-item f-14 text-dark save-form" href="javascript:;" data-type="save">
                                <i class="fa fa-save f-w-500 mr-2 f-11"></i>
                                @if ($invoice->status == 'draft')
                                    @lang('app.saveDraft')
                                @else
                                    @lang('app.save')
                                @endif
                            </a>
                        </li>
                        @if($invoice->status == 'draft' || $invoice->send_status == 0)
                            <li>
                                <a class="dropdown-item f-14 text-dark save-form" href="javascript:void(0);"
                                   data-type="send">
                                    <i class="fa fa-paper-plane f-w-500  mr-2 f-12"></i> @lang('app.saveSend')
                                </a>
                            </li>
                        @endif
                        @if($invoice->status == 'draft' || $invoice->send_status == 0)
                            <li>
                                <a class="dropdown-item f-14 text-dark save-form" href="javascript:void(0);"
                                   data-type="mark_as_send" data-toggle="tooltip" data-original-title="@lang('messages.markSentInfo')">
                                    <i class="fa fa-check-double f-w-500  mr-2 f-12"></i> @lang('app.saveMark')
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>

                <x-forms.button-cancel :link="route('invoices.index')" class="border-0">@lang('app.cancel')
                </x-forms.button-cancel>
            </div>


        </x-form-actions>
        <!-- CANCEL SAVE SEND END -->

    </x-form>
    <!-- FORM END -->
</div>
<!-- CREATE INVOICE END -->
<!-- for sortable content -->
<script src="{{ asset('vendor/jquery/jquery-ui.min.js') }}"></script>

<script>
    $(function () {
        $("#sortable").sortable();
    });

    $(document).ready(function() {
        $('.toggle-product-category').click(function() {
            $('.product-category-filter').toggleClass('d-none');
            var url = "{{route('invoices.product_category', ':id')}}";
            url = url.replace(':id', null);
            changeProductCategory(url);
            $('#product_category_id').val('').trigger('change');
            $('#product_category_id').selectpicker('refresh');
        });

        $('#product_category_id').on('change', function(){
            var categoryId = $(this).val();
            var url = "{{route('invoices.product_category', ':id')}}";
            url = (categoryId) ? url.replace(':id', categoryId) : url.replace(':id', null);
            changeProductCategory(url);
        });

        function changeProductCategory(url) {
            $.easyAjax({
                url : url,
                type : "GET",
                container: '#saveInvoiceForm',
                blockUI: true,
                success: function (response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            if (value.opening_stock > 0) {
                                selectData = '<option value="' + value.id + '">' + value.name +
                                '</option>';
                                options.push(selectData);
                            }
                        });
                        $('#add-products').html(
                            '<option value="" class="form-control" >{{  __('app.menu.selectProduct') }}</option>' +
                            options);
                        $('#add-products').selectpicker('refresh');
                    }
                }
            });
        }

        Dropzone.autoDiscover = false;
            //Dropzone class
        invoiceDropzone = new Dropzone("div#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('invoice-files.store') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: DROPZONE_MAX_FILES,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: DROPZONE_MAX_FILES,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function() {
                invoiceDropzone = this;
            }
        });
        invoiceDropzone.on('sending', function(file, xhr, formData) {
            var ids = "{{ $invoice->id }}";
            formData.append('invoice_id', ids);
            $.easyBlockUI();
        });
        invoiceDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        invoiceDropzone.on('queuecomplete', function() {
            var msgs = "@lang('messages.recordSaved')";
            window.location.href = "{{ route('invoices.index') }}"
        });
        invoiceDropzone.on('removedfile', function () {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        invoiceDropzone.on('error', function (file, message) {
            invoiceDropzone.removeFile(file);
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).find(".help-block").remove();
            var helpBlockContainer = $(grp);

            if (helpBlockContainer.length == 0) {
                helpBlockContainer = $(grp);
            }

            helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
            $(grp).addClass("has-error");
            $(label).addClass("is-invalid");

        });

        var file = $('#sortable .dropify').dropify({
            messages: dropifyMessages
        });

        file.on('dropify.beforeClear', function(event, element) {

            let invoice_item_id = $(this).data('id');
            let file_path = $(this).data('default-file');

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {

                    var url = "{{ route('invoices.delete_image') }}";
                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'get',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': token,
                            'invoice_item_id': invoice_item_id,
                            'file_path': file_path
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                element.resetPreview();
                            }
                        }
                    });
                }
            });

            return false;
        });

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        const dp1 = datepicker('#invoice_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $invoice->issue_date) }}"),
            ...datepickerConfig
        });
        const dp2 = datepicker('#due_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $invoice->due_date) }}"),
            ...datepickerConfig
        });

        $('#client_list_id').change(function() {
            var id = $(this).val();
            var url = "{{ route('clients.project_list', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#project_id').html(response.data);
                        $('#project_id').selectpicker('refresh');
                    }
                }
            });

            var url = "{{ route('clients.ajax_details', ':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                url: url,
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#client_billing_address').html(nl2br(response.data.clientDetails
                            .address));
                        $('#add-shipping-field').addClass('d-none');
                        $('#client_shipping_address').removeClass('d-none');

                        if (response.data.clientDetails.shipping_address === null) {
                            var addShippingLink =
                                `<a href="javascript:;" class="" id="show-shipping-field"><i class="f-12 mr-2 fa fa-plus"></i>
                                    @lang("app.addShippingAddress")</a>`;
                            $('#client_shipping_address').html(addShippingLink);
                        } else {
                            $('#client_shipping_address').html(nl2br(response.data
                                .clientDetails
                                .shipping_address));
                        }
                    }
                }
            });

        });

        $('body').on('click', '#show-shipping-field', function() {
            $('#add-shipping-field, #client_shipping_address').toggleClass('d-none');
        });

        const resetAddProductButton = () => {
            $("#add-products").val('').selectpicker("refresh");
        };

        $('#add-products').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
            e.stopImmediatePropagation()
            var id = $(this).val();
            if (previousValue != id && id != '') {
                addProduct(id);
                resetAddProductButton();
            }
        });



        function addProduct(id) {
            var currencyId = $('#currency_id').val();
            var exchangeRate = $('#exchange_rate').val();
            $.easyAjax({
                url: "{{ route('invoices.add_item') }}",
                type: "GET",
                data: {
                    id: id,
                    currencyId: currencyId,
                    exchangeRate: exchangeRate
                },
                blockUI: true,
                success: function(response) {
                    if($('input[name="item_name[]"]').val() == ''){
                        $("#sortable .item-row").remove();
                    }
                    $(response.view).hide().appendTo("#sortable").fadeIn(500);
                    calculateTotal();

                    var noOfRows = $(document).find('#sortable .item-row').length;
                    var i = $(document).find('.item_name').length - 1;
                    var itemRow = $(document).find('#sortable .item-row:nth-child(' + noOfRows +
                        ') select.type');
                    itemRow.attr('id', 'multiselect' + i);
                    itemRow.attr('name', 'taxes[' + i + '][]');
                    $(document).find('#multiselect' + i).selectpicker();
                }
            });
        }

        $(document).on('click', '#add-item', function() {

            var i = $(document).find('.item_name').length;
            var item = ' <div class="d-flex px-4 py-3 c-inv-desc item-row">' +
                `<div class="d-flex align-items-center">
                    <span class="ui-icon ui-icon-arrowthick-2-n-s mr-2"></span>
                    <input type="hidden" name="sort_order[]"
                            value="${i+1}">
                </div>` +
                '<div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">' +
                '<table width="100%">' +
                '<tbody>' +
                '<tr class="text-dark-grey font-weight-bold f-14">' +
                '<td width="{{ $invoiceSetting->hsn_sac_code_show ? '40%' : '50%' }}" class="border-0 inv-desc-mbl btlr">@lang("app.description")</td>';

            @if ($invoiceSetting->hsn_sac_code_show)
                item += '<td width="10%" class="border-0" align="right">@lang("app.hsnSac")</td>';
            @endif

                item +=
                `<td width="10%" class="border-0" align="right">@lang("modules.invoices.qty")</td>
                <td width="10%" class="border-0" align="right">@lang("modules.invoices.unitPrice")</td>
                <td width="13%" class="border-0" align="right">@lang("modules.invoices.tax")</td>
                <td width="17%" class="border-0 bblr-mbl" align="right">@lang("modules.invoices.amount")</td>
                </tr>
                <tr>
                <td class="border-bottom-0 btrr-mbl btlr">
                <input type="text" class="f-14 border-0 w-100 item_name form-control" name="item_name[]" placeholder="@lang("modules.expenses.itemName")">
                </td>
                <td class="border-bottom-0 d-block d-lg-none d-md-none">
                <textarea class="f-14 border-0 w-100 mobile-description form-control" name="item_summary[]" placeholder="@lang("placeholders.invoices.description")"></textarea>
                </td>`;

            @if ($invoiceSetting->hsn_sac_code_show)
                item += '<td class="border-bottom-0">' +
                '<input type="text" min="1" class="f-14 border-0 w-100 text-right hsn_sac_code form-control" value="" name="hsn_sac_code[]">'
                +
                '</td>';
            @endif

                item += '<td class="border-bottom-0">' +
                '<input type="number" min="1" class="f-14 border-0 w-100 text-right quantity form-control mt-3" value="1" name="quantity[]">' +
                `<select class="text-dark-grey float-right border-0 f-12" name="unit_id[]">
                    @foreach ($units as $unit)
                        <option
                        @if ($unit->default == 1) selected @endif
                        value="{{ $unit->id }}">{{ $unit->unit_type }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="product_id[]" value="">`+
                '</td>' +
                '<td class="border-bottom-0">' +
                '<input type="number" min="1" class="f-14 border-0 w-100 text-right cost_per_item form-control" placeholder="0.00" value="0" name="cost_per_item[]">' +
                '</td>' +
                '<td class="border-bottom-0">' +
                '<div class="select-others height-35 rounded border-0">' +
                '<select id="multiselect' + i + '" name="taxes[' + i +
                '][]" multiple="multiple" class="select-picker type customSequence" data-size="3">'
                @foreach ($taxes as $tax)
                +'<option data-rate="{{ $tax->rate_percent }}" data-tax-text="{{ $tax->tax_name .':'. $tax->rate_percent }}%" value="{{ $tax->id }}">'
                +'{{ $tax->tax_name }}:{{ $tax->rate_percent }}%</option>'
                @endforeach
                +
                '</select>' +
                '</div>' +
                '</td>' +
                '<td rowspan="2" align="right" valign="top" class="bg-amt-grey btrr-bbrr">' +
                '<span class="amount-html">0.00</span>' +
                '<input type="hidden" class="amount" name="amount[]" value="0">' +
                '</td>' +
                '</tr>' +
                '<tr class="d-none d-md-table-row d-lg-table-row">' +
                '<td colspan="{{ $invoiceSetting->hsn_sac_code_show ? 4 : 3 }}" class="dash-border-top bblr">' +
                '<textarea class="f-14 border-0 w-100 desktop-description" name="item_summary[]" placeholder="@lang("placeholders.invoices.description")"></textarea>' +
                '</td>' +
                '<td td class="border-left-0">' +
                '<input type="file" class="dropify" id="dropify'+i+'" name="invoice_item_image[]" data-allowed-file-extensions="png jpg jpeg bmp" data-messages-default="test" data-height="70""/><input type="hidden" name="invoice_item_image_url[]">' +
                '</td>' +
                '</tr>' +
                '</tbody>' +
                '</table>' +
                '</div>' +
                '<a href="javascript:;" class="d-flex align-items-center justify-content-center ml-3 remove-item"><i class="fa fa-times-circle f-20 text-lightest"></i></a>' +
                '</div>';
            $(item).hide().appendTo("#sortable").fadeIn(500);
            $('#multiselect' + i).selectpicker();

            $(document).find('#dropify' + i).dropify({
                messages: dropifyMessages
            });
        });

        $('#saveInvoiceForm').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
                $('select.customSequence').each(function(index) {
                    $(this).attr('name', 'taxes[' + index + '][]');
                    $(this).attr('id', 'multiselect' + index + '');
                });
                calculateTotal();
            });
        });

        $('.save-form').click(function() {
            var type = $(this).data('type');
            let totalAmt = $('.total-field').val();

            if((type == 'send' || type == 'mark_as_send') && totalAmt == 0) {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.markAsPaid')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('app.yes')",
                    cancelButtonText: "@lang('app.no')",
                    customClass: {
                        confirmButton: 'btn btn-primary mr-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        saveForm(type, 'direct');
                    }
                });
            } else {
                saveForm(type, 'direct');
            }

        });

        $('#saveInvoiceForm').on('keyup', '.quantity,.cost_per_item,.item_name, .discount_value', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveInvoiceForm').on('change', '.type, #discount_type, #calculate_tax', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveInvoiceForm').on('input', '.quantity', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        calculateTotal();

        <x-forms.custom-field-filejs/>

        init(RIGHT_MODAL);

    });

    function ucWord(str){
        str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
            return letter.toUpperCase();
        });
        return str;
    }

    $('#currency_id').change(function() {
        var curId = $(this).val();
        var companyCurrencyName = "{{$companyCurrency->currency_code}}";
        var currentCurrencyName = $('#currency_id option:selected').attr('data-currency-code');
        var companyCurrency = '{{ $companyCurrency->id }}';

        if(curId == companyCurrency){
            $('#exchange_rate').prop('readonly', true);
        } else{
            $('#exchange_rate').prop('readonly', false);
        }
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: "{{ route('payments.account_list') }}",
            container: '#saveInvoiceForm',
            type: "GET",
            blockUI: true,
            data: { 'curId' : curId , _token: token},
            success: function(response) {
                if (response.status == 'success') {
                    $('#bank_account_id').html(response.data);
                    $('#bank_account_id').selectpicker('refresh');
                    $('#exchange_rate').val(response.exchangeRate);
                    let currencyExchange = (companyCurrencyName != currentCurrencyName) ? '( '+currentCurrencyName+' @lang('app.to') '+companyCurrencyName+' )' : '';
                    $('#currency_exchange').html(currencyExchange);
                }
            }
        });
    });

    $('input[type=checkbox][name=payment_status]').change(function() {
        if ($(this).is(":checked")) {
            $(this).val(1);
            $('#add_offline').addClass('d-none');
            $('.payment-types').removeClass('d-none');
        } else {
            $(this).val(0);
            $('#add_offline').addClass('d-none');
            $('#transaction_id').val('');
            $('.payment-types').addClass('d-none');
            $('#payment_gateway_id').val('');
            $('#payment_gateway_id').selectpicker('refresh');
        }
    });

    $('#payment_gateway_id').on('change', function()
    {
        var val = $(this).val();

        if (val == 'Offline'){
            var url = "{{ route('offline.methods') }}"
            $.easyAjax({
                url : url,
                type : "GET",
                success: function (response) {
                    if (response.status == 'success') {
                        $('#add_offline').removeClass('d-none');
                        var options = [];
                        var rData = [];
                        rData = response.data;
                            $.each(rData, function (index, value) {
                            var selectData = '';
                            if(value.status=='yes'){
                            selectData = '<option value="' + value.id + '">' + value.name + '</option>';
                            }
                            options.push(selectData);
                        });
                        $('#add_offline_methods').html(
                            options);
                        $('#add_offline_methods').selectpicker('refresh');
                    }
                }
            });
        }
        else
        {
            $('#add_offline').addClass('d-none');
        }
    });

    function saveForm(type, exceed){

            $('#doItLater').val(exceed);

            if (KTUtil.isMobileDevice()) {
                $('.desktop-description').remove();
            } else {
                $('.mobile-description').remove();
            }

            calculateTotal();

            var discount = $('#discount_amount').html();
            var total = $('.sub-total-field').val();

            if (parseFloat(discount) > parseFloat(total)) {
                Swal.fire({
                    icon: 'error',
                    text: "{{ __('messages.discountExceed') }}",

                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                    buttonsStyling: false
                });
                return false;
            }

            $.easyAjax({
                url: "{{ route('invoices.update', $invoice->id) }}" + "?type=" + type,
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                file: true,  // Commented so that we dot get error of Input variables exceeded 1000
                data: $('#saveInvoiceForm').serialize(),
                success: function(response) {
                    $(MODAL_DEFAULT).modal('hide');

                    if (response.status == 'error' && response.showValue === true && exceed == 'direct') {
                        const productIDs = response.data;
                        $('#do_it_later').val('true');
                        const url = "{{ route('invoices.committed_modal') }}" + "?products=" + productIDs + "&type=" + type ;

                        $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
                        $.ajaxModal(MODAL_DEFAULT, url);
                    }

                    if (response.status === 'success') {
                        if (typeof invoiceDropzone !== 'undefined' && invoiceDropzone.getQueuedFiles().length > 0) {
                            invoiceID = response.invoiceID;
                            $('#invoiceID').val(response.invoiceID);
                            invoiceDropzone.processQueue();
                        }
                        else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            })
        }

</script>

@endif
