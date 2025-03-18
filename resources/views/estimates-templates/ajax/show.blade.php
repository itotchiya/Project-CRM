
<style>
    #logo {
        height: 50px;
    }

    .signature_wrap {
        position: relative;
        height: 150px;
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        user-select: none;
        width: 400px;
    }

    .signature-pad {
        position: absolute;
        left: 0;
        top: 0;
        width: 400px;
        height: 150px;
    }

    .mt{
        margin-top: -8px;
    }

    .ml{
    padding: 8px!important;
    border-radius: 4px;
    margin-top: -9px!important
}

</style>

<!-- INVOICE CARD START -->

<div class="card border-0 invoice">
    <!-- CARD BODY START -->
    <div class="card-body">
        <div class="invoice-table-wrapper">
            <table width="100%" class="">
                <tr class="inv-logo-heading">
                    <td><img src="{{ invoice_setting()->logo_url }}" alt="{{ company()->company_name }}"
                            id="logo" /></td>
                    <td align="right" class="font-weight-bold f-21 text-dark text-uppercase mt-4 mt-lg-0 mt-md-0">
                        @lang('modules.estimates.estimateTemplate')</td>
                </tr>
                <tr class="inv-num">
                    <td class="f-14 text-dark">
                        <p class="mt-3 mb-0">
                            {{ company()->company_name }}<br>
                            @if (!is_null($settings))
                                {!! nl2br(default_address()->address) !!}<br>
                                {{ company()->company_phone }}
                            @endif
                            @if ($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                                <br>@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}
                            @endif
                        </p><br>
                    </td>
                    <td align="right">

                    </td>
                </tr>
                <tr>
                    <td height="20"></td>
                </tr>
            </table>
            <table width="100%">
                <tr class="inv-unpaid">
                    <td class="f-14 text-dark">
                        @if ($invoice->lead && ($invoice->lead->client_name || $invoice->lead->client_email || $invoice->lead->mobile || $invoice->lead->company_name || $invoice->lead->address) && (invoice_setting()->show_client_name == 'yes' || invoice_setting()->show_client_email == 'yes' || invoice_setting()->show_client_phone == 'yes' || invoice_setting()->show_client_company_name == 'yes' || invoice_setting()->show_client_company_address == 'yes'))
                        <p class="mb-0 text-left">
                            <span class="text-dark-grey ">
                                @lang("modules.invoices.billedTo")
                            </span><br>

                            @if ($invoice->lead && $invoice->lead->client_name && invoice_setting()->show_client_name == 'yes')
                                {{ $invoice->lead->client_name_salutation }}<br>
                            @endif
                            @if ($invoice->lead && $invoice->lead->client_email && invoice_setting()->show_client_email == 'yes')
                                {{ $invoice->lead->client_email }}<br>
                            @endif
                            @if ($invoice->lead && $invoice->lead->mobile && invoice_setting()->show_client_phone == 'yes')
                                {{ $invoice->lead->mobile }}<br>
                            @endif
                            @if ($invoice->lead && $invoice->lead->company_name && invoice_setting()->show_client_company_name == 'yes')
                                {{ $invoice->lead->company_name }}<br>
                            @endif
                            @if ($invoice->lead && $invoice->lead->address && invoice_setting()->show_client_company_address == 'yes')
                                {!! nl2br($invoice->lead->address) !!}
                            @endif
                        </p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td height="30" colspan="2"></td>
                </tr>
            </table>
            <div class="d-flex flex-column">
                <h5>@lang('app.name')</h5>
                <p class="f-15">{{ $invoice->name }}</p>
                <br>
                <h5>@lang('app.description')</h5>
                <div class="ql-editor p-0 pb-3">{!! $invoice->description !!}</div>
            <table width="100%" class="inv-desc d-none d-lg-table d-md-table mt-5">
                <tr>
                    <td colspan="2">
                        <table class="inv-detail f-14 table-responsive-sm" width="100%">
                            <tr class="i-d-heading bg-light-grey text-dark-grey font-weight-bold">
                                <td class="border-right-0" width="35%">@lang('app.description')</td>
                                @if($invoiceSetting->hsn_sac_code_show == 1)
                                    <td class="border-right-0 border-left-0" align="right">@lang("app.hsnSac")</td>
                                @endif
                                <td class="border-right-0 border-left-0" align="right">@lang('modules.invoices.qty')</td>
                                <td class="border-right-0 border-left-0" align="right">
                                    @lang("modules.invoices.unitPrice") ({{ $invoice->currency->currency_code }})
                                </td>
                                <td class="border-right-0 border-left-0" align="right">
                                    @lang("modules.invoices.tax")
                                </td>
                                <td class="border-left-0" align="right">
                                    @lang("modules.invoices.amount")
                                    ({{ $invoice->currency->currency_code }})</td>
                            </tr>


                            @foreach ($invoice->items as $item)

                                @if ($item->type == 'item')
                                    <tr class="text-dark font-weight-semibold f-13">
                                        <td>{{ $item->item_name }}</td>
                                        @if($invoiceSetting->hsn_sac_code_show == 1)
                                            <td align="right">{{ $item->hsn_sac_code }}</td>
                                        @endif
                                        <td align="right">{{ $item->quantity }} @if($item->unit)<br><span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>@endif</td>
                                        <td align="right">
                                            {{ currency_format($item->unit_price, $invoice->currency_id, false) }}
                                        </td>
                                        <td align="right">{{ $item->tax_list }}</td>
                                        <td align="right">{{ currency_format($item->amount, $invoice->currency_id, false) }}
                                        </td>
                                    </tr>
                                    @if ($item->item_summary || $item->estimateTemplateItemImage)
                                        <tr class="text-dark f-12">
                                            <td colspan="6" class="border-bottom-0">
                                                {!! nl2br(strip_tags($item->item_summary)) !!}
                                                @if ($item->estimateTemplateItemImage)
                                                    <p class="mt-2">
                                                        <a href="javascript:;" class="img-lightbox" data-image-url="{{ $item->estimateTemplateItemImage->file_url }}">
                                                            <img src="{{ $item->estimateTemplateItemImage->file_url }}" width="80" height="80" class="img-thumbnail">
                                                        </a>
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                            @endforeach

                            <tr>
                                <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '4' : '3' }}" class="blank-td border-bottom-0 border-left-0 border-right-0"></td>
                                <td class="p-0 border-right-0" align="right">
                                    <table width="100%">
                                        <tr class="text-dark-grey" align="right">
                                            <td class="w-50 border-top-0 border-left-0">
                                                @lang("modules.invoices.subTotal")</td>
                                        </tr>
                                        @if ($discount != 0 && $discount != '')
                                            <tr class="text-dark-grey" align="right">
                                                <td class="w-50 border-top-0 border-left-0">
                                                    @lang("modules.invoices.discount")</td>
                                            </tr>
                                        @endif
                                        @foreach ($taxes as $key => $tax)
                                            <tr class="text-dark-grey" align="right">
                                                <td class="w-50 border-top-0 border-left-0">
                                                    {{ $key }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light-grey text-dark f-w-500 f-16" align="right">
                                            <td class="w-50 border-bottom-0 border-left-0">
                                                @lang("modules.invoices.total")</td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="p-0 border-right-0" align="right">
                                    <table width="100%">
                                        <tr class="text-dark-grey" align="right">
                                            <td class="border-top-0 border-left-0">
                                                {{ currency_format($invoice->sub_total, $invoice->currency_id, false) }}</td>
                                        </tr>
                                        @if ($discount != 0 && $discount != '')
                                            <tr class="text-dark-grey" align="right">
                                                <td class="border-top-0 border-left-0">
                                                    {{ currency_format($discount, $invoice->currency_id, false) }}</td>
                                            </tr>
                                        @endif
                                        @foreach ($taxes as $key => $tax)
                                            <tr class="text-dark-grey" align="right">
                                                <td class="border-top-0 border-left-0">
                                                    {{ currency_format($tax, $invoice->currency_id, false) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light-grey text-dark f-w-500 f-16" align="right">
                                            <td class="border-bottom-0 border-left-0">
                                                {{ currency_format($invoice->total, $invoice->currency_id, false) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>

                </tr>
            </table>
            <table width="100%" class="inv-desc-mob d-block d-lg-none d-md-none">

                @foreach ($invoice->items as $item)
                    @if ($item->type == 'item')

                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang('app.description')</th>
                            <td class="p-0 ">
                                <table>
                                    <tr width="100%" class="font-weight-semibold f-13">
                                        <td class="border-left-0 border-right-0 border-top-0">
                                            {{ $item->item_name }}</td>
                                    </tr>
                                    @if ($item->item_summary != '' || $item->estimateTemplateItemImage)
                                        <tr>
                                            <td class="border-left-0 border-right-0 border-bottom-0 f-12">
                                                {!! nl2br(strip_tags($item->item_summary)) !!}
                                                @if ($item->estimateTemplateItemImage)
                                                    <p class="mt-2">
                                                        <a href="javascript:;" class="img-lightbox" data-image-url="{{ $item->estimateTemplateItemImage->file_url }}">
                                                            <img src="{{ $item->estimateTemplateItemImage->file_url }}" width="80" height="80" class="img-thumbnail">
                                                        </a>
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang('modules.invoices.qty')</th>
                            <td width="50%">{{ $item->quantity }} @if($item->unit)<br><span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>@endif</td>
                        </tr>
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang("modules.invoices.unitPrice")
                                ({{ $invoice->currency->currency_code }})</th>
                            <td width="50%">{{ currency_format($item->unit_price, $invoice->currency_id, false) }}</td>
                        </tr>
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang("modules.invoices.amount")
                                ({{ $invoice->currency->currency_code }})</th>
                            <td width="50%">{{ currency_format($item->amount, $invoice->currency_id, false) }}</td>
                        </tr>
                        <tr>
                            <td height="3" class="p-0 " colspan="2"></td>
                        </tr>
                    @endif
                @endforeach

                <tr>
                    <th width="50%" class="text-dark-grey font-weight-normal">@lang("modules.invoices.subTotal")
                    </th>
                    <td width="50%" class="text-dark-grey font-weight-normal">
                        {{ currency_format($invoice->sub_total, $invoice->currency_id, false) }}</td>
                </tr>
                @if ($discount != 0 && $discount != '')
                    <tr>
                        <th width="50%" class="text-dark-grey font-weight-normal">@lang("modules.invoices.discount")
                        </th>
                        <td width="50%" class="text-dark-grey font-weight-normal">
                            {{ currency_format($discount, $invoice->currency_id, false) }}</td>
                    </tr>
                @endif

                @foreach ($taxes as $key => $tax)
                    <tr>
                        <th width="50%" class="text-dark-grey font-weight-normal">{{ $key }}</th>
                        <td width="50%" class="text-dark-grey font-weight-normal">
                            {{ currency_format($tax, $invoice->currency_id, false) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th width="50%" class="text-dark-grey font-weight-bold">@lang("modules.invoices.total")</th>
                    <td width="50%" class="text-dark-grey font-weight-bold">
                        {{ currency_format( $invoice->total, $invoice->currency_id, false) }}</td>
                </tr>
            </table>
            <table class="inv-note">
                <tr>
                    <td height="30" colspan="2"></td>
                </tr>
                <tr>
                    <td align="right">
                        <table>
                            <tr>@lang('modules.invoiceSettings.invoiceTerms')</tr>
                            <tr>
                                <p class="text-dark-grey">{!! nl2br($invoiceSetting->invoice_terms) !!}</p>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        @if (isset($taxes) && invoice_setting()->tax_calculation_msg == 1)
                            <p class="text-dark-grey">
                                @if ($invoice->calculate_tax == 'after_discount')
                                    @lang('messages.calculateTaxAfterDiscount')
                                @else
                                    @lang('messages.calculateTaxBeforeDiscount')
                                @endif
                            </p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        @if ($invoice->signature)
            <div class="row">
                <div class="col-sm-12 mt-4">
                    @if (!is_null($invoice->signature->signature))
                        <img src="{{ $invoice->signature->signature }}" style="width: 200px;">
                        <h6>@lang('modules.estimates.signature')</h6>
                    @else
                        <h6>@lang('modules.estimates.signedBy')</h6>
                    @endif
                    <p>({{ $invoice->signature->full_name }})</p>
                </div>
            </div>
        @endif

         @if ($invoice->client_comment)
            <hr>
            <div class="col-md-12">
                <h4 class="name" style="margin-bottom: 20px;">@lang('app.rejectReason')</h4>
                <p> {{ $invoice->client_comment }} </p>
            </div>
        @endif

    </div>
    <!-- CARD BODY END -->
    <!-- CARD FOOTER START -->
    <div class="card-footer bg-white border-0 d-flex justify-content-start py-0 py-lg-4 py-md-4 mb-4 mb-lg-3 mb-md-3 ">
        <x-forms.button-cancel :link="route('estimate-template.index')" class="border-0 ml-2 mt">
            @lang('app.cancel')
        </x-forms.button-cancel>
    </div>
    <!-- CARD FOOTER END -->
</div>
<!-- INVOICE CARD END -->
