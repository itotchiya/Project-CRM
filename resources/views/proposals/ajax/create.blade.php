@php
    $addProductPermission = user()->permission('add_product');
    $addLeadPermission = user()->permission('add_lead');
@endphp

<!-- for sortable content -->
<link rel="stylesheet" href="{{ asset('vendor/css/jquery-ui.css') }}">

    <!-- CREATE INVOICE START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-lg-4 px-md-4 px-3 py-3">
        <h4 class="mb-0 f-21 font-weight-normal ">@lang('app.proposalDetails')
        </h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="saveInvoiceForm">
        <!-- INVOICE NUMBER, DATE, DUE DATE, FREQUENCY START -->
        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <!-- CLIENT START -->
            <input type="hidden" name="template_id" value="{{ $proposalTemplate->id ?? '' }}">
            @if (isset($deal) && !is_null($deal))
                <div class="col-md-6 col-lg-3">
                    <x-forms.label fieldId="client_id" :fieldLabel="__('modules.deal.title')" fieldRequired="true"
                                   class="mt-3"/>
                    <x-forms.input-group>
                        <input type="text" readonly
                               class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                               placeholder="{{ $deal->name }}"
                               value="{{ $deal->name }}">
                    </x-forms.input-group>
                                        <input type="hidden" name="deal_id" value="{{ $deal->id }}">
                </div>
                <!-- CLIENT END -->
            @else
                <div class="col-lg-4 ">
                    <x-forms.select fieldId="lead_contact" :fieldLabel="__('modules.leadContact.leadContacts')" search="true"
                                    fieldName="lead_contact" fieldRequired="true">
                        <option value="">--</option>
                        @foreach ($leadContacts as $leadContact)
                            <option value="{{ $leadContact->id }}">
                                {{ $leadContact->client_name_salutation }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
                <div class="col-lg-4 ">
                    <x-forms.label fieldId="client_id" :fieldLabel="__('modules.deal.title')" fieldRequired="true"
                                   class="mt-3"/>
                    <select name="deal_id" id="deal_id" class="form-control select-picker" data-live-search="true"
                            data-size="8">
                        <option value="">--</option>
                        @foreach ($deals as $clientOpt)
                            <option @if ($proposalTemplate && $clientOpt->id == $proposalTemplate->deal_id) selected
                                    @endif value="{{ $clientOpt->id }}">
                                {{ $clientOpt->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <!-- INVOICE DATE START -->
            <div class="col-md-6 col-lg-3">
                <div class="form-group mb-lg-0 mb-md-0 mb-4 mt-3">
                    <x-forms.label fieldId="due_date" :fieldLabel="__('modules.estimates.validTill')"
                                   fieldRequired="true">
                    </x-forms.label>
                    <x-forms.input-group>
                        <input type="text" id="valid_till" name="valid_till"
                               class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                               placeholder="@lang('placeholders.date')"
                               value="{{ Carbon\Carbon::today()->addDays(30)->format(company()->date_format) }}">
                    </x-forms.input-group>
                </div>
            </div>
            <!-- INVOICE DATE END -->

            <!-- FREQUENCY START -->
            <div class="col-md-6 col-lg-3">
                <x-forms.select :fieldLabel="__('modules.invoices.currency')" fieldName="currency_id"
                                field_id="currency_id">
                    @foreach ($currencies as $currency)
                        <option @if (isset($proposalTemplate) && $currency->id == $proposalTemplate->currency_id)
                                    selected
                                @elseif ($currency->id == company()->currency_id)
                                    selected
                                @endif
                                value="{{ $currency->id }}">
                            {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                        </option>
                    @endforeach
                </x-forms.select>
            </div>

            <!-- FREQUENCY END -->

            <div class="col-md-6 col-lg-3">
                <div class="form-group c-inv-select mb-lg-0 mb-md-0 mb-4 mt-3">
                    <x-forms.label fieldId="calculate_tax" :fieldLabel="__('modules.invoices.calculateTax')">
                    </x-forms.label>
                    <div class="select-others height-35 rounded">
                        @if (isset($proposalTemplate) && !is_null($proposalTemplate))
                            <select class="form-control select-picker" data-live-search="true" data-size="8"
                                    name="calculate_tax" id="calculate_tax">
                                <option value="after_discount">@lang('modules.invoices.afterDiscount')</option>
                                <option value="before_discount"
                                        @if ($proposalTemplate->calculate_tax == 'before_discount') selected @endif>
                                    @lang('modules.invoices.beforeDiscount')</option>
                            </select>
                        @else
                            <select class="form-control select-picker" data-live-search="true" data-size="8"
                                    name="calculate_tax" id="calculate_tax">
                                <option value="after_discount">@lang('modules.invoices.afterDiscount')</option>
                                <option value="before_discount">
                                    @lang('modules.invoices.beforeDiscount')</option>
                            </select>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-12 my-3">
                <div class="form-group">
                    <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                    </x-forms.label>
                    <div id="description">{!! $proposalTemplate ? $proposalTemplate->description : '' !!}</div>
                    <textarea name="description" id="description-text" class="d-none"></textarea>
                </div>
            </div>

            <!-- FREQUENCY START -->
            <div class="col-md-6">
                <x-forms.checkbox :fieldLabel="__('modules.proposal.requireSignature')" fieldName="require_signature"
                                  fieldId="require_signature" fieldValue="true" checked="true"/>
            </div>
            <!-- FREQUENCY END -->

        </div>
        <!-- INVOICE NUMBER, DATE, DUE DATE, FREQUENCY END -->

        <hr class="m-0 border-top-grey">

        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <div class="col-md-3 d-none product-category-filter">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.input-group>
                        <select class="form-control select-picker" name="category_id"
                                id="product_category_id" data-live-search="true">
                            <option
                                value="">{{ __('app.select') . ' ' . __('app.product') . ' ' . __('app.category')  }}</option>
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
                            <select class="form-control select-picker" data-live-search="true" data-size="8"
                                    id="add-products" title="{{ __('app.menu.selectProduct') }}">
                                @foreach ($products as $item)
                                    <option data-content="{{ $item->name }}" value="{{ $item->id }}">
                                        {{ $item->name }}</option>
                                @endforeach
                            </select>
                            <x-slot name="preappend">
                                <a href="javascript:;"
                                   class="btn btn-outline-secondary border-grey toggle-product-category"
                                   data-toggle="tooltip"
                                   data-original-title="{{ __('modules.productCategory.filterByCategory') }}"><i
                                        class="fa fa-filter"></i></a>
                            </x-slot>
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

        </div>

        <div id="sortable">
            @if (isset($estimate))
                @foreach ($estimate->items as $key => $item)
                    <!-- DESKTOP DESCRIPTION TABLE START -->
                    <div class="d-flex px-4 py-3 c-inv-desc item-row">
                        <div class="d-flex align-items-center">
                            <span class="ui-icon ui-icon-arrowthick-2-n-s mr-2"></span>
                            <input type="hidden" name="sort_order[]"
                                    value="1">
                        </div>

                        <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                            <table width="100%">
                                <tbody>
                                <tr class="text-dark-grey font-weight-bold f-14">
                                    <td width="40%" class="border-0 inv-desc-mbl btlr">@lang('app.description')</td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td width="10%" class="border-0" align="right">@lang("app.hsnSac")
                                        </td>
                                    @endif
                                    <td width="10%" class="border-0" align="right">
                                        @lang('modules.invoices.qty')
                                    </td>
                                    <td width="10%" class="border-0" align="right">
                                        @lang("modules.invoices.unitPrice")</td>
                                    <td width="13%" class="border-0" align="right">
                                        @lang('modules.invoices.tax')
                                    </td>
                                    <td width="17%" class="border-0 bblr-mbl" align="right">
                                        @lang('modules.invoices.amount')</td>
                                </tr>
                                <tr>
                                    <td class="border-bottom-0 btrr-mbl btlr">
                                        <input type="text" class="f-14 border-0 w-100 item_name form-control"
                                               name="item_name[]"
                                               placeholder="@lang('modules.expenses.itemName')"
                                               value="{{ $item->item_name }}">
                                    </td>
                                    <td class="border-bottom-0 d-block d-lg-none d-md-none">
                                            <textarea class="f-14 border-0 w-100 mobile-description form-control"
                                                      placeholder="@lang('placeholders.invoices.description')"
                                                      name="item_summary[]">{{ $item->item_summary }}</textarea>
                                    </td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td class="border-bottom-0">
                                            <input type="text" min="1"
                                                   class="f-14 border-0 w-100 text-right hsn_sac_code form-control"
                                                   value="{{ $item->hsn_sac_code }}" name="hsn_sac_code[]">
                                        </td>
                                    @endif
                                    <td class="border-bottom-0">
                                        <input type="number" min="1"
                                               class="f-14 border-0 w-100 text-right quantity form-control mt-3"
                                               value="{{ $item->quantity }}" name="quantity[]">
                                        @if (!is_null($item->product_id) && $item->product_id != 0)
                                            <span
                                                class="text-dark-grey float-right border-0 f-12">{{ $item->unit->unit_type }}</span>
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
                                               class="f-14 border-0 w-100 text-right cost_per_item form-control"
                                               placeholder="0.00"
                                               value="{{ $item->unit_price }}" name="cost_per_item[]">
                                    </td>
                                    <td class="border-bottom-0">
                                        <div class="select-others height-35 rounded border-0">
                                            <select id="multiselect" name="taxes[{{ $key }}][]"
                                                    multiple="multiple"
                                                    class="select-picker type customSequence border-0" data-size="3">
                                                @foreach ($taxes as $tax)
                                                    <option data-rate="{{ $tax->rate_percent }}"
                                                            data-tax-text="{{ $tax->tax_name .':'. $tax->rate_percent }}%"
                                                            @if (isset($item->taxes) && array_search($tax->id, json_decode($item->taxes)) !== false) selected
                                                            @endif value="{{ $tax->id }}">
                                                        {{ $tax->tax_name }}:
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
                                            <textarea class="f-14 border-0 w-100 desktop-description form-control"
                                                      name="item_summary[]"
                                                      placeholder="@lang('placeholders.invoices.description')">{{ $item->item_summary }}</textarea>
                                    </td>
                                    <td class="border-left-0">
                                        <input type="file" class="dropify" name="invoice_item_image[]"
                                               data-allowed-file-extensions="png jpg jpeg bmp"
                                               data-messages-default="test" data-height="70"/>
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
            @elseif (isset($proposalTemplateItem) && !is_null($proposalTemplateItem))

                @foreach ($proposalTemplateItem as $key => $item)
                    <!-- DESKTOP DESCRIPTION TABLE START -->
                    <div class="d-flex px-4 py-3 c-inv-desc item-row">
                        <div class="d-flex align-items-center">
                            <span class="ui-icon ui-icon-arrowthick-2-n-s mr-2"></span>
                            <input type="hidden" name="sort_order[]"
                                    value="1">
                        </div>

                        <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                            <table width="100%">
                                <tbody>
                                <tr class="text-dark-grey font-weight-bold f-14">
                                    <td width="40%" class="border-0 inv-desc-mbl btlr">@lang('app.description')</td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td width="10%" class="border-0" align="right">@lang("app.hsnSac")</td>
                                    @endif
                                    <td width="10%" class="border-0" align="right">
                                        @lang('modules.invoices.qty')
                                    </td>
                                    <td width="10%" class="border-0" align="right">
                                        @lang("modules.invoices.unitPrice")
                                    </td>
                                    <td width="13%" class="border-0" align="right">@lang('modules.invoices.tax')
                                    </td>
                                    <td width="17%" class="border-0 bblr-mbl" align="right">
                                        @lang('modules.invoices.amount')</td>
                                </tr>
                                <tr>
                                    <td class="border-bottom-0 btrr-mbl btlr">
                                        <input type="text" class="f-14 border-0 w-100 item_name form-control"
                                               name="item_name[]"
                                               placeholder="@lang('modules.expenses.itemName')"
                                               value="{{ $item->item_name }}">
                                    </td>
                                    @if ($invoiceSetting->hsn_sac_code_show)
                                        <td class="border-bottom-0">
                                            <input type="text"
                                                   class="f-14 border-0 w-100 text-right hsn_sac_code form-control"
                                                   value="{{  $item->hsn_sac_code }}" name="hsn_sac_code[]">
                                        </td>
                                    @endif
                                    <td class="border-bottom-0">
                                        <input type="number" min="1"
                                               class="f-14 border-0 w-100 text-right quantity form-control mt-3"
                                               name="quantity[]" value="{{ $item->quantity }}">
                                        @if (!is_null($item->product_id) && $item->product_id != 0)
                                            <span
                                                class="text-dark-grey float-right border-0 f-12">{{ $item->unit->unit_type }}</span>
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
                                               class="f-14 border-0 w-100 text-right cost_per_item form-control"
                                               placeholder="0.00"
                                               name="cost_per_item[]" value="{{ $item->unit_price }}">
                                    </td>
                                    <td class="border-bottom-0">
                                        <div class="select-others height-35 rounded border-0">
                                            <select id="multiselect" name="taxes[{{$key}}][]" multiple="multiple"
                                                    class="select-picker type customSequence border-0" data-size="3">
                                                @foreach ($taxes as $tax)
                                                    <option data-rate="{{ $tax->rate_percent }}"
                                                            data-tax-text="{{ $tax->tax_name .':'. $tax->rate_percent }}%"
                                                            @if (isset($item->taxes) && array_search($tax->id, json_decode($item->taxes)) !== false) selected
                                                            @endif value="{{ $tax->id }}">{{ $tax->tax_name }}
                                                        {{ $tax->rate_percent }}%
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td rowspan="2" align="right" valign="top" class="bg-amt-grey btrr-bbrr">
                                        <span
                                            class="amount-html">{{ number_format((float) $item->amount, 2, '.', '')}}</span>
                                        <input type="hidden" class="amount" name="amount[]" value="{{ $item->amount }}">
                                    </td>
                                </tr>
                                <tr class="d-none d-md-table-row d-lg-table-row">
                                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '4' : '3' }}"
                                        class="dash-border-top bblr">
                                            <textarea class="f-14 border-0 w-100 desktop-description form-control"
                                                      name="item_summary[]"
                                                      placeholder="@lang('placeholders.invoices.description')">{{ $item->item_summary }}</textarea>
                                    </td>
                                    <td class="border-left-0">
                                        <input type="hidden" id="imageId_{{ $item->id }}"
                                               class="itemOldImage" name="image_id[]"
                                               value={{ isset($item->proposalTemplateItemImage ->id) ? $item->proposalTemplateItemImage ->id : '' }} />
                                        <input type="file"
                                               class="dropify"
                                               name="invoice_item_image[]"
                                               data-allowed-file-extensions="png jpg jpeg bmp"
                                               data-messages-default="test"
                                               data-height="70"
                                               data-id="{{ $item->id }}"
                                               id="{{ $item->id }}"
                                               data-default-file="{{ $item->proposalTemplateItemImage ? $item->proposalTemplateItemImage->file_url : '' }}"
                                               @if ($item->proposalTemplateItemImage && $item->proposalTemplateItemImage->external_link)
                                                   data-show-remove="false"
                                            @endif
                                        />
                                        <input type="hidden" name="invoice_item_image_url[]"
                                               value="{{ $item->proposalTemplateItemImage ? $item->proposalTemplateItemImage->file : '' }}">
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
                    <div class="d-flex align-items-center">
                        <span class="ui-icon ui-icon-arrowthick-2-n-s mr-2"></span>
                        <input type="hidden" name="sort_order[]"
                                value="1">
                    </div>

                    <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                        <table width="100%">
                            <tbody>
                            <tr class="text-dark-grey font-weight-bold f-14">
                                <td width="40%" class="border-0 inv-desc-mbl btlr">@lang('app.description')</td>
                                @if ($invoiceSetting->hsn_sac_code_show)
                                    <td width="10%" class="border-0" align="right">@lang("app.hsnSac")</td>
                                @endif
                                <td width="10%" class="border-0" align="right">
                                    @lang('modules.invoices.qty')
                                </td>
                                <td width="10%" class="border-0" align="right">
                                    @lang("modules.invoices.unitPrice")
                                </td>
                                <td width="13%" class="border-0" align="right">@lang('modules.invoices.tax')
                                </td>
                                <td width="17%" class="border-0 bblr-mbl" align="right">
                                    @lang('modules.invoices.amount')</td>
                            </tr>
                            <tr>
                                <td class="border-bottom-0 btrr-mbl btlr">
                                    <input type="text" class="f-14 border-0 w-100 item_name form-control"
                                           name="item_name[]"
                                           placeholder="@lang('modules.expenses.itemName')">
                                </td>
                                <td class="border-bottom-0 d-block d-lg-none d-md-none">
                                        <textarea class="f-14 border-0 w-100 mobile-description form-control"
                                                  name="item_summary[]"
                                                  placeholder="@lang('placeholders.invoices.description')"></textarea>
                                </td>
                                @if ($invoiceSetting->hsn_sac_code_show)
                                    <td class="border-bottom-0">
                                        <input type="text"
                                               class="f-14 border-0 w-100 text-right hsn_sac_code form-control"
                                               value="" name="hsn_sac_code[]">
                                    </td>
                                @endif
                                <td class="border-bottom-0">
                                    <input type="number" min="1"
                                           class="f-14 border-0 w-100 text-right quantity form-control mt-3"
                                           value="1" name="quantity[]">
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
                                           class="f-14 border-0 w-100 text-right cost_per_item form-control"
                                           placeholder="0.00"
                                           value="0" name="cost_per_item[]">
                                </td>
                                <td class="border-bottom-0">
                                    <div class="select-others height-35 rounded border-0">
                                        <select id="multiselect" name="taxes[0][]" multiple="multiple"
                                                class="select-picker type customSequence border-0" data-size="3">
                                            @foreach ($taxes as $tax)
                                                <option data-rate="{{ $tax->rate_percent }}"
                                                        data-tax-text="{{ $tax->tax_name .':'. $tax->rate_percent }}%"
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
                                        <textarea class="f-14 border-0 w-100 desktop-description form-control"
                                                  name="item_summary[]"
                                                  placeholder="@lang('placeholders.invoices.description')"></textarea>
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
                    <td width="50%" class="p-0 border-0">
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
                                    <table width="100%" class="mw-250">
                                        <tbody>
                                        <tr>
                                            <td width="70%" class="c-inv-sub-padding">
                                                <input type="number" min="0" name="discount_value"
                                                       class="f-14 border-0 w-100 text-right discount_value"
                                                       placeholder="0"
                                                       value="{{ isset($proposalTemplate) ? $proposalTemplate->discount : '0' }}">
                                            </td>
                                            <td width="30%" align="left" class="c-inv-sub-padding">
                                                <div
                                                    class="select-others select-tax height-35 rounded border-0">
                                                    <select class="form-control select-picker"
                                                            id="discount_type" name="discount_type">
                                                        <option
                                                            @if (isset($proposalTemplate) && $proposalTemplate->discount_type == 'percent') selected
                                                            @endif value="percent">%
                                                        </option>
                                                        <option
                                                            @if (isset($proposalTemplate) && $proposalTemplate->discount_type == 'fixed') selected
                                                            @endif value="fixed">
                                                            @lang('modules.invoices.amount')</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td>
                                            <span
                                                id="discount_amount">{{ isset($proposalTemplate) ? number_format((float) $proposalTemplate->discount, 2, '.', '') : '0.00' }}
                                            </span>
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
                <textarea class="form-control" name="note" id="lead_note" rows="4"
                          placeholder="@lang('placeholders.invoices.note')"></textarea>
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
                                <i class="fa fa-save f-w-500 mr-2 f-11"></i> @lang('app.save')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item f-14 text-dark save-form" href="javascript:void(0);"
                               data-type="send">
                                <i class="fa fa-paper-plane f-w-500  mr-2 f-12"></i> @lang('app.saveSend')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item f-14 text-dark save-form" href="javascript:void(0);"
                               data-type="mark_as_send" data-toggle="tooltip"
                               data-original-title="@lang('messages.markSentInfo')">
                                <i class="fa fa-check-double f-w-500  mr-2 f-12"></i> @lang('app.saveMark')
                            </a>
                        </li>
                    </ul>
                </div>

                <x-forms.button-cancel :link="route('proposals.index')" class="border-0">@lang('app.cancel')
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
    $(document).ready(function () {

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
                            selectData = '<option value="' + value.id + '">' + value.name +
                                '</option>';
                            options.push(selectData);
                        });
                        $('#add-products').html(
                            '<option value="" class="form-control" >{{  __('app.menu.selectProduct') }}</option>' +
                            options);
                        $('#add-products').selectpicker('refresh');
                    }
                }
            });
        }

        $('#lead_contact').change(function (e) {

            let contactId = $(this).val();

            var url = "{{ route('deals.get-deals', ':id') }}";
            url = url.replace(':id', contactId);

            $.easyAjax({
                url: url,
                type: "GET",
                success: function (response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function (index, value) {
                            var selectData;
                            selectData = '<option value="' + value.id + '">' + value.name + '</option>';
                            options.push(selectData);
                        });

                        $('#deal_id').html('<option value="">--</option>' + options);
                        $('#deal_id').selectpicker('refresh');
                    }
                }
            })

        });

        const hsn_status = {{ $invoiceSetting->hsn_sac_code_show }};
        const dp1 = datepicker('#valid_till', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', now()->addDays(30)) }}"),
            ...datepickerConfig
        });

        quillMention(null, '#description');

        const resetAddProductButton = () => {
            $("#add-products").val('').selectpicker("refresh");
        };

        $('#add-products').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            e.stopImmediatePropagation()
            var id = $(this).val();
            if (previousValue != id && id != '') {
                addProduct(id);
                resetAddProductButton();
            }
        });

        function ucWord(str) {
            str = str.toLowerCase().replace(/\b[a-z]/g, function (letter) {
                return letter.toUpperCase();
            });
            return str;
        }

        function addProduct(id) {
            var currencyId = $('#currency_id').val();

            $.easyAjax({
                url: "{{ route('proposals.add_item') }}",
                type: "GET",
                data: {
                    id: id,
                    currencyId: currencyId
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

                    $(document).find('#dropify' + i).dropify({
                        messages: dropifyMessages
                    });
                }
            });
        }

        $(document).on('click', '#add-item', function () {

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
                <input type="hidden" name="product_id[]" value="">` +
                '</td>' +
                '<td class="border-bottom-0">' +
                '<input type="number" min="1" class="f-14 border-0 w-100 text-right cost_per_item" placeholder="0.00" value="0" name="cost_per_item[]">' +
                '</td>' +
                '<td class="border-bottom-0">' +
                '<div class="select-others height-35 rounded border-0">' +
                '<select id="multiselect' + i + '" name="taxes[' + i +
                '][]" multiple="multiple" class="select-picker type customSequence" data-size="3">'
                @foreach ($taxes as $tax)
                + '<option data-rate="{{ $tax->rate_percent }}" data-tax-text="{{ $tax->tax_name .':'. $tax->rate_percent }}%" value="{{ $tax->id }}">'
                + '{{ $tax->tax_name }}:{{ $tax->rate_percent }}%</option>'
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
                '<input type="file" class="dropify" id="dropify' + i + '" name="invoice_item_image[]" data-allowed-file-extensions="png jpg jpeg bmp" data-messages-default="test" data-height="70" /><input type="hidden" name="invoice_item_image_url[]">' +
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

        $('.save-form').click(function () {
            let note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

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
                url: "{{ route('proposals.store') }}" + "?type=" + type,
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                file: true,  // Commented so that we dot get error of Input variables exceeded 1000
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
</script>
