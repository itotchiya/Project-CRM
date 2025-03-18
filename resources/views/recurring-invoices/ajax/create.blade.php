@php
    $addProductPermission = user()->permission('add_product');
@endphp
<style>
    .customSequence .btn {
        border: none;
    }

    .information-box {
        border-style: dotted;
        margin-bottom: 30px;
        margin-top:10px;
        padding-top: 10px;
        border-radius: 4px;
    }
</style>

@if (!in_array('clients', user_modules()))
    <x-alert class="mb-3" type="danger" icon="exclamation-circle"><span>@lang('messages.enableClientModule')</span>
        <x-forms.link-secondary icon="arrow-left" :link="route('recurring-invoices.index')">@lang('app.back')</x-forms.link-secondary>
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

        <!-- INVOICE NUMBER, DATE, DUE DATE, FREQUENCY START -->
        <div class="row px-lg-4 px-md-4 px-3 pt-3">
            <!-- CLIENT START -->
            <div class="col-md-3 mb-4">

                <x-client-selection-dropdown :clients="$clients" />

            </div>
            <!-- CLIENT END -->
            <!-- PROJECT START -->
            <div class="col-md-3">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="project_id" :fieldLabel="__('app.project')">
                    </x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" data-live-search="true" data-size="8"
                                name="project_id" id="project_id">
                            <option value="">--</option>
                            @if (isset($estimate) && $estimate->client)
                                @foreach ($estimate->client->projects as $item)
                                    <option value="{{ $item->id }}">{{ $item->project_name }}</option>
                                @endforeach
                            @endif

                        </select>
                    </div>
                </div>
            </div>
            <!-- PROJECT END -->
            <div class="col-md-3">
                <div class="form-group c-inv-select mb-lg-0 mb-md-0 mb-4">
                    <x-forms.label fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')">
                    </x-forms.label>

                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="currency_id" id="currency_id">
                            @foreach ($currencies as $currency)
                                <option @if (isset($estimate))
                                        @if ($currency->id==$estimate->currency_id) selected @endif
                                        @else
                                        @if ($currency->id == company()->currency_id)
                                        selected @endif
                                        @endif
                                        value="{{ $currency->id }}">
                                    {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group mb-lg-0 mb-4">
                    <x-forms.label fieldId="calculate_tax" :fieldLabel="__('modules.invoices.calculateTax')">
                    </x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" data-live-search="true" data-size="8"
                                name="calculate_tax" id="calculate_tax">
                            <option value="after_discount">@lang('modules.invoices.afterDiscount')</option>
                            <option value="before_discount">@lang('modules.invoices.beforeDiscount')</option>
                        </select>
                    </div>
                </div>
            </div>


            @if($linkInvoicePermission == 'all')
                <div class="col-md-3">
                    <div class="form-group c-inv-select">
                        <x-forms.label fieldId="bank_account_id" :fieldLabel="__('app.bankaccount')">
                        </x-forms.label>
                        <div class="select-others height-35 rounded">
                            <select class="form-control select-picker" data-live-search="true" data-size="8"
                                    name="bank_account_id" id="bank_account_id">
                                <option value="">--</option>
                                @if($viewBankAccountPermission != 'none')
                                    @foreach ($bankDetails as $bankDetail)
                                        <option value="{{ $bankDetail->id }}">@if($bankDetail->type == 'bank')
                                            {{ $bankDetail->bank_name }} | @endif
                                            {{ $bankDetail->account_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-3"></div>
            @endif
        </div>
        <!-- INVOICE NUMBER, DATE, DUE DATE, FREQUENCY END -->



        <div class="row px-lg-4 px-md-4 px-3 py-4">
            <div class="col-md-3">
                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2"
                                  :fieldLabel="__('modules.recurringInvoice.allowToClient')" fieldName="client_can_stop"
                                  fieldId="client_can_stop" fieldValue="true" fieldRequired="true" checked="true"/>
            </div>
            <div class="col-lg-3">
                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2"
                                  :fieldLabel="__('modules.invoices.showShippingAddress')"
                                  fieldName="show_shipping_address"
                                  fieldId="show_shipping_address"
                                  :popover="__('modules.invoices.showShippingAddressInfo')"
                                  fieldValue="true" />

            </div>
            <!-- SHIPPING ADDRESS START -->
            <div class="col-md-4 d-none" id="shipping_address_div">
                <div class="form-group c-inv-select mb-lg-0 mb-md-0 mb-4">
                    <label class="f-14 text-dark-grey mb-12  w-100"
                           for="usr">@lang('modules.invoices.shippingAddress')</label>
                    <p>
                        <textarea class="form-control f-14 pt-2" rows="3" placeholder="@lang('placeholders.address')"
                                  name="shipping_address"
                                  id="shipping_address">@if (isset($estimate) && $estimate->client) {!! nl2br($estimate->client->clientDetails->shipping_address) !!} @endif</textarea>
                    </p>
                </div>
            </div>
            <!-- SHIPPING ADDRESS END -->
        </div>

        <hr class="m-0 border-top-grey">
        <div class="row px-lg-4 px-md-4 px-3 pt-3">
            <!-- BILLING FREQUENCY -->
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4 mt-4">
                        <x-forms.label fieldId="rotation" :fieldLabel="__('modules.invoices.billingFrequency')"
                                    fieldRequired="true">
                        </x-forms.label>
                        <div class="form-group c-inv-select">
                            <select class="form-control select-picker" data-live-search="true" data-size="8" name="rotation"
                                    id="rotation">
                                <option value="daily">@lang('app.daily')</option>
                                <option value="weekly">@lang('app.weekly')</option>
                                <option value="bi-weekly">@lang('app.bi-weekly')</option>
                                <option value="monthly">@lang('app.monthly')</option>
                                <option value="quarterly">@lang('app.quarterly')</option>
                                <option value="half-yearly">@lang('app.half-yearly')</option>
                                <option value="annually">@lang('app.annually')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8 mt-4">
                        <div class="form-group">
                            <div class="d-flex">
                                <x-forms.label class="mr-3" fieldId="start_date" :fieldLabel="__('app.startDate')">
                                </x-forms.label>
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2 mt-0"
                                                :fieldLabel="__('modules.recurringInvoice.immediateInvoice')"
                                                fieldName="immediate_invoice"
                                                fieldId="immediate_invoice" fieldValue="true" fieldRequired="true"/>
                            </div>
                            <div class="input-group">
                                <input type="text" id="start_date" name="issue_date"
                                    class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                    placeholder="@lang('placeholders.date')"
                                    value="{{ now(company()->timezone)->format(company()->date_format) }}">
                            </div>
                            <small class="form-text text-muted">@lang('modules.recurringInvoice.invoiceDate')</small>
                        </div>
                    </div>
                    <div class="col-lg-4 mt-0">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2 mt-0" :fieldLabel="__('modules.invoices.totalCount')"
                                        fieldName="billing_cycle" fieldId="billing_cycle"
                                        :fieldHelp="__('modules.invoices.noOfBillingCycle')" fieldRequired="true"/>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mt-4 information-box">
                <p id="plan">@lang('app.customerChargedDaily')</p>
                <p id="current_date">@lang('modules.recurringInvoice.currentInvoiceDate') <span class="font-weight-bold">{{now()->translatedFormat(company()->date_format)}}</span></p>
                <p id="next_date">@lang('modules.recurringInvoice.nextInvoiceDate') <span class="font-weight-bold">{{now()->addDay()->translatedFormat(company()->date_format)}}</span></p>
                <p>@lang('modules.recurringInvoice.soOn')</p>
                <span id="billing"></span>
            </div>

        </div>

        <hr class="m-0 border-top-grey">

        @if(in_array('products', user_modules()) || in_array('purchase', user_modules()))
            <div class="d-flex px-4 py-3">
                <div class="form-group">
                    <x-forms.input-group>
                        <select class="form-control select-picker" data-live-search="true" data-size="8" id="add-products" title="{{ __('app.menu.selectProduct') }}">
                            @foreach ($products as $item)
                                <option data-content="{{ $item->title }}" value="{{ $item->id }}">
                                    {{ $item->title }}</option>
                            @endforeach
                        </select>
                        @if ($addProductPermission == 'all' || $addProductPermission == 'added')
                            <x-slot name="append">
                                <a href="{{ route('products.create') }}" data-redirect-url="no"
                                class="btn btn-outline-secondary border-grey openRightModal"
                                data-toggle="tooltip"
                                data-original-title="{{ __('app.add').' '.__('modules.dashboard.newproduct') }}">@lang('app.add')</a>
                            </x-slot>
                        @endif
                    </x-forms.input-group>

                </div>
            </div>
        @endif

        <div id="sortable">
        @if (isset($estimate))
            @foreach ($estimate->items as $key => $item)
                <!-- DESKTOP DESCRIPTION TABLE START -->
                    <div class="d-flex px-4 py-3 c-inv-desc item-row">

                        <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                            <table width="100%">
                                <tbody>
                                <tr class="text-dark-grey font-weight-bold f-14">
                                    <td width="{{ $invoiceSetting->hsn_sac_code_show ? '40%' : '50%' }}"
                                        class="border-0 inv-desc-mbl btlr">@lang('app.description')</td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td width="10%" class="border-0" align="right">@lang("app.hsnSac")</td>
                                    @endif
                                    <td width="10%" class="border-0" align="right">
                                        @lang('modules.invoices.qty')
                                    </td>
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
                                            <textarea class="f-14 border-0 w-100 mobile-description"
                                                      placeholder="@lang('placeholders.invoices.description')"
                                                      name="item_summary[]">{{ $item->item_summary }}</textarea>
                                    </td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td class="border-bottom-0">
                                            <input type="text"
                                                   class="form-control f-14 border-0 w-100 text-right hsn_sac_code"
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
                                               class="f-14 border-0 w-100 text-right cost_per_item" placeholder="0.00"
                                               value="{{ $item->unit_price }}" name="cost_per_item[]">
                                    </td>
                                    <td class="border-bottom-0">
                                        <div class="select-others height-35 rounded border-0">
                                            <select id="multiselect" name="taxes[{{ $key }}][]"
                                                    multiple="multiple"
                                                    class="select-picker type customSequence border-0" data-size="3">
                                                @foreach ($taxes as $tax)
                                                    <option data-rate="{{ $tax->rate_percent }}" data-tax-text="{{ $tax->tax_name .':'. $tax->rate_percent }}%"
                                                            @if (isset($item->taxes) && array_search($tax->id, json_decode($item->taxes)) !== false) selected
                                                            @endif
                                                            value="{{ $tax->id }}">{{ $tax->tax_name }}:
                                                        {{ $tax->rate_percent }}%
                                                    </option>
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
                                            <textarea class="f-14 border-0 w-100 desktop-description"
                                                      name="item_summary[]"
                                                      placeholder="@lang('placeholders.invoices.description')">{{ $item->item_summary }}</textarea>
                                    </td>
                                    <td class="border-left-0">
                                        <input type="file" class="dropify" name="invoice_item_image[]"
                                               data-allowed-file-extensions="png jpg jpeg bmp" data-messages-default="test"
                                               data-height="70"/>
                                        <input type="hidden" name="invoice_item_image_url[]">
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
        @else
            <!-- DESKTOP DESCRIPTION TABLE START -->
                <div class="d-flex px-4 py-3 c-inv-desc item-row">

                    <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                        <table width="100%">
                            <tbody>
                            <tr class="text-dark-grey font-weight-bold f-14">
                                <td width="{{ $invoiceSetting->hsn_sac_code_show ? '40%' : '50%' }}"
                                    class="border-0 inv-desc-mbl btlr">@lang('app.description')</td>
                                @if ($invoiceSetting->hsn_sac_code_show)
                                    <td width="10%" class="border-0" align="right">@lang("app.hsnSac")</td>
                                @endif
                                <td width="10%" class="border-0" align="right">@lang('modules.invoices.qty')</td>
                                <td width="10%" class="border-0" align="right">@lang("modules.invoices.unitPrice")
                                </td>
                                <td width="13%" class="border-0" align="right">@lang('modules.invoices.tax')</td>
                                <td width="17%" class="border-0 bblr-mbl" align="right">
                                    @lang('modules.invoices.amount')</td>
                            </tr>
                            <tr>
                                <td class="border-bottom-0 btrr-mbl btlr">
                                    <input type="text" class="form-control f-14 border-0 w-100 item_name"
                                           name="item_name[]" placeholder="@lang('modules.expenses.itemName')">
                                </td>
                                <td class="border-bottom-0 d-block d-lg-none d-md-none">
                                    <input type="text" class="f-14 border-0 w-100 mobile-description"
                                           name="item_summary[]"
                                           placeholder="@lang('placeholders.invoices.description')">
                                </td>
                                @if ($invoiceSetting->hsn_sac_code_show)
                                    <td class="border-bottom-0">
                                        <input type="text"
                                               class="form-control f-14 border-0 w-100 text-right hsn_sac_code"
                                               value="" name="hsn_sac_code[]">
                                    </td>
                                @endif
                                <td class="border-bottom-0">
                                    <input type="number" min="1"
                                           class="form-control f-14 border-0 w-100 text-right quantity mt-3" value="1"
                                           name="quantity[]">

                                    <select class="text-dark-grey float-right border-0 f-12" name="unit_id[]">
                                        @foreach ($units as $unit)
                                            <option
                                            value="{{ $unit->id }}">{{ $unit->unit_type }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="product_id[]" value="">
                                </td>
                                <td class="border-bottom-0">
                                    <input type="number" min="1"
                                           class="f-14 border-0 w-100 text-right cost_per_item" placeholder="0.00"
                                           value="0" name="cost_per_item[]">
                                </td>
                                <td class="border-bottom-0">
                                    <div class="select-others height-35 rounded border-0">
                                        <select id="multiselect" name="taxes[0][]" multiple="multiple"
                                                class="select-picker type customSequence border-0" data-size="3">
                                            @foreach ($taxes as $tax)
                                                <option data-rate="{{ $tax->rate_percent }}" data-tax-text="{{ $tax->tax_name .':'. $tax->rate_percent }}%"
                                                        value="{{ $tax->id }}">{{ $tax->tax_name }}:
                                                    {{ $tax->rate_percent }}%
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td rowspan="2" align="right" valign="top" class="bg-amt-grey btrr-bbrr">
                                    <span class="amount-html">0.00</span>
                                    <input type="hidden" class="amount" name="amount[]" value="0">
                                </td>
                            </tr>
                            <tr class="d-none d-md-table-row d-lg-table-row">
                                <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '4' : '3' }}"
                                    class="dash-border-top bblr">
                                    <input type="text" class="form-control f-14 border-0 w-100 desktop-description"
                                           name="item_summary[]"
                                           placeholder="@lang('placeholders.invoices.description')">
                                </td>
                                <td class="border-left-0">
                                    <input type="file" class="dropify" name="invoice_item_image[]"
                                           data-allowed-file-extensions="png jpg jpeg bmp" data-messages-default="test"
                                           data-height="70"/>
                                    <input type="hidden" name="invoice_item_image_url[]">
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
            @endif

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
                                <td width="30%" class="border-top-0 sub-total">0.00</td>
                                <input type="hidden" class="sub-total-field" name="sub_total" value="0">
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
                                                       placeholder="0"
                                                       value="{{ isset($estimate) ? $estimate->discount : '0' }}">
                                            </td>
                                            <td width="30%" align="left" class="c-inv-sub-padding">
                                                <div
                                                    class="select-others select-tax height-35 rounded border-0">
                                                    <select class="form-control select-picker"
                                                            id="discount_type" name="discount_type">
                                                        <option
                                                            @if (isset($estimate) && $estimate->discount_type == 'percent') selected
                                                            @endif
                                                            value="percent">%
                                                        </option>
                                                        <option
                                                            @if (isset($estimate) && $estimate->discount_type == 'fixed') selected
                                                            @endif
                                                            value="fixed">
                                                            @lang('modules.invoices.amount')</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td><span
                                        id="discount_amount">{{ isset($estimate) ? number_format((float) $estimate->discount, 2, '.', '') : '0.00' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('modules.invoices.tax')</td>
                                <td colspan="2" class="p-0 border-0">
                                    <table width="100%" id="invoice-taxes">
                                        <tr>
                                            <td colspan="2"><span class="tax-percent">0.00</span></td>
                                        </tr>
                                    </table>
                                </td>

                            </tr>
                            <tr class="bg-amt-grey f-16 f-w-500">
                                <td colspan="2">@lang('modules.invoices.total')</td>
                                <td><span class="total">0.00</span></td>
                                <input type="hidden" class="total-field" name="total" value="0">
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
                          placeholder="@lang('placeholders.invoices.note')"></textarea>
            </div>
            <div class="col-md-6 col-sm-12 p-0 c-inv-note-terms">
                <label class="f-14 text-dark-grey mb-12  w-100"
                       for="usr">@lang('modules.invoiceSettings.invoiceTerms')</label>
                <p>
                    {!! nl2br($invoiceSetting->invoice_terms) !!}
                </p>
            </div>
        </div>
        <!-- NOTE AND TERMS AND CONDITIONS END -->


        <x-form-actions>
            <x-forms.button-primary id="save-form" icon="check">@lang('app.save')</x-forms.button-primary>

            <x-forms.button-cancel :link="route('recurring-invoices.index')" class="border-0 ml-3">@lang('app.cancel')
            </x-forms.button-cancel>

        </x-form-actions>

    </x-form>
    <!-- FORM END -->
</div>
<!-- CREATE INVOICE END -->

@endif
<script>
    $(document).ready(function () {


        const hsn_status = {{ $invoiceSetting->hsn_sac_code_show }};
        const defaultClient = "{{ request('default_client') }}";

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                var rotation = $('#rotation').val();
                nextDate(rotation, date);
            },
            ...datepickerConfig
        });

        $('#client_list_id').change(function () {
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
                success: function (response) {
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
                success: function (response) {
                    if (response.status == 'success') {
                        $('#client_billing_address').html(nl2br(response.data.client_details.address));
                        $('#add-shipping-field').addClass('d-none');
                        $('#client_shipping_address').removeClass('d-none');

                        if (response.data.client_details.shipping_address === null) {
                            var addShippingLink =
                                '<a href="javascript:;" class="" id="show-shipping-field"><i class="f-12 mr-2 fa fa-plus"></i>@lang("app.addShippingAddress")</a>';
                            $('#client_shipping_address').html(addShippingLink);
                        } else {
                            $('#client_shipping_address').html(nl2br(response.data.client_details.shipping_address));
                        }
                    }
                }
            });

        });

        $('body').on('click', '#show-shipping-field', function () {
            $('#add-shipping-field, #client_shipping_address').toggleClass('d-none');
        });

        $('#add-products').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            e.stopImmediatePropagation()
            var id = $(this).val();
            if (previousValue != id && id != '') {
                addProduct(id);
            }
        });

        function ucWord(str){
            str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                return letter.toUpperCase();
            });
            return str;
        }

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
                success: function (response) {
                    if ($('input[name="item_name[]"]').val() == '') {
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
                '<div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">' +
                '<table width="100%">' +
                '<tbody>' +
                '<tr class="text-dark-grey font-weight-bold f-14">' +
                '<td width="{{ $invoiceSetting->hsn_sac_code_show ? '40%' : '50%' }}" class="border-0 inv-desc-mbl btlr">@lang("app.description")</td>';

            if (hsn_status == 1) {
                item += '<td width="10%" class="border-0" align="right">@lang("app.hsnSac")</td>';
            }

            item +=
                `
                <td width="10%" class="border-0" align="right">@lang("modules.invoices.qty")</td>
                <td width="10%" class="border-0" align="right">@lang("modules.invoices.unitPrice")</td>
                <td width="13%" class="border-0" align="right">@lang("modules.invoices.tax")</td>
                <td width="17%" class="border-0 bblr-mbl" align="right">@lang("modules.invoices.amount")</td>
                </tr>` +
                '<tr>' +
                '<td class="border-bottom-0 btrr-mbl btlr">' +
                `<input type="text" class="form-control f-14 border-0 w-100 item_name" name="item_name[]" placeholder="@lang("modules.expenses.itemName")">` +
                '</td>' +
                '<td class="border-bottom-0 d-block d-lg-none d-md-none">' +
                `<textarea class="f-14 border-0 w-100 mobile-description form-control" name="item_summary[]" placeholder="@lang("placeholders.invoices.description")"></textarea>` +
                '</td>';

            if (hsn_status == 1) {
                item += '<td class="border-bottom-0">' +
                    '<input type="text" min="1" class="form-control f-14 border-0 w-100 text-right hsn_sac_code" name="hsn_sac_code[]" >' +
                    '</td>';
            }
            item += '<td class="border-bottom-0">' +
                '<input type="number" min="1" class="form-control f-14 border-0 w-100 text-right quantity mt-3" value="1" name="quantity[]">' +
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
                '<input type="number" min="1" class="f-14 border-0 w-100 text-right cost_per_item" placeholder="0.00" value="0" name="cost_per_item[]">' +
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
                '<textarea class="f-14 border-0 w-100 desktop-description form-control" name="item_summary[]" placeholder="@lang("placeholders.invoices.description")"></textarea>' +
                '</td>' +
                '<td class="border-left-0">' +
                '<input type="file" class="dropify" id="dropify'+i+'" name="invoice_item_image[]" data-allowed-file-extensions="png jpg jpeg bmp" data-messages-default="test" data-height="70" /><input type="hidden" name="invoice_item_image_url[]">' +
                '</td>' +
                '</tr>' +
                '</tbody>' +
                '</table>' +
                '</div>' +
                '<a href="javascript:;" class="d-flex align-items-center justify-content-center ml-3 remove-item"><i class="fa fa-times-circle f-20 text-lightest"></i></a>' +
                '</div>';
            $(item).hide().appendTo("#sortable").fadeIn(500);
            $('#multiselect' + i).selectpicker();

            $('#dropify' + i).dropify({
                messages: dropifyMessages
            });
        });

        $('#saveInvoiceForm').on('click', '.remove-item', function () {
            $(this).closest('.item-row').fadeOut(300, function () {
                $(this).remove();
                $('select.customSequence').each(function (index) {
                    $(this).attr('name', 'taxes[' + index + '][]');
                    $(this).attr('id', 'multiselect' + index + '');
                });
                calculateTotal();
            });
        });

        $('#save-form').click(function () {
            var type = $(this).data('type');

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
                url: "{{ route('recurring-invoices.store') }}" + "?type=" + type,
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                disableButton: true,
                file: true,
                data: $('#saveInvoiceForm').serialize()
            })
        });

        $('#saveInvoiceForm').on('click', '.remove-item', function () {
            $(this).closest('.item-row').fadeOut(300, function () {
                $(this).remove();
                $('select.customSequence').each(function (index) {
                    $(this).attr('name', 'taxes[' + index + '][]');
                    $(this).attr('id', 'multiselect' + index + '');
                });
                calculateTotal();
            });
        });

        $('#saveInvoiceForm').on('keyup', '.quantity,.cost_per_item,.item_name, .discount_value', function () {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveInvoiceForm').on('change', '.type, #discount_type, #calculate_tax', function () {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveInvoiceForm').on('input', '.quantity', function () {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        calculateTotal();

        init(RIGHT_MODAL);
    });

    $('#show_shipping_address').change(function () {
        $(this).is(':checked') ? $('#shipping_address_div').removeClass('d-none') : $('#shipping_address_div')
            .addClass('d-none');
    });

    $('body').on('change keyup', '#rotation, #billing_cycle', function () {
        var billingCycle = $('#billing_cycle').val();
        billingCycle != '' ? $('#billing').html("{{__('modules.recurringInvoice.billingCycle')}}" + ' ' + billingCycle) : $('#billing').html('');

        var rotation = $('#rotation').val();
        switch (rotation) {
            case 'daily':
                var rotationType = "{{__('app.daily')}}";
                break;
            case 'weekly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('modules.recurringInvoice.week')}}";
                break;
            case 'bi-weekly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.bi-week')}}";
                break;
            case 'monthly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.month')}}";
                break;
            case 'quarterly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.quarter')}}";
                break;
            case 'half-yearly':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.half-year')}}";
                break;
            case 'annually':
                var rotationType = "{{__('app.every')}}"+' '+"{{__('app.year')}}";
                break;
            default:
            //
        }

        $('#plan').html("{{__('modules.invoices.customerCharged')}}" + ' <span class="font-weight-bold">' + rotationType + '</span>');

        if ($('#immediate_invoice').is(':checked')) {
            var date = moment().toDate();
        } else {
            var startDate = $('#start_date').val();
            var date = moment(startDate, company.moment_date_format).toDate();
        }

        nextDate(rotation, date);
    })

    $('#immediate_invoice').change(function () {
        var rotation = $('#rotation').val();

        if ($(this).is(':checked')) {
            var date = moment().toDate();
            $('#start_date').val(moment(date, "DD-MM-YYYY").format('{{ company()->moment_date_format }}'));
            $('#start_date').prop('disabled', true)
        } else {
            $('#start_date').prop('disabled', false)
            var startDate = $('#start_date').val();
            var date = moment(startDate, company.moment_date_format).toDate();
        }

        nextDate(rotation, date);

    })

    function nextDate(rotation, date) {
        var nextDate = moment(date, "DD-MM-YYYY");
        var currentValue = nextDate.format('{{ company()->moment_date_format }}');

        switch (rotation) {
            case 'daily':
                var rotationDate = nextDate.add(1, 'days');
                break;
            case 'weekly':
                var rotationDate = nextDate.add(1, 'weeks');
                break;
            case 'bi-weekly':
                var rotationDate = nextDate.add(2, 'weeks');
                break;
            case 'monthly':
                var rotationDate = nextDate.add(1, 'months');
                break;
            case 'quarterly':
                var rotationDate = nextDate.add(1, 'quarters');
                break;
            case 'half-yearly':
                var rotationDate = nextDate.add(2, 'quarters');
                break;
            case 'annually':
                var rotationDate = nextDate.add(1, 'years');
                break;
            default:
            //
        }

        var value = rotationDate.format('{{ company()->moment_date_format }}');

        $('#current_date').html("{{__('modules.recurringInvoice.currentInvoiceDate')}}" + ' <span class="font-weight-bold">' + currentValue + '</span>');

        $('#next_date').html("{{__('modules.recurringInvoice.nextInvoiceDate')}}" + ' <span class="font-weight-bold">' + value + '</span>');
    }

    $('#currency_id').change(function() {
        var curId = $(this).val();

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
                }
            }
        });
    });

</script>
