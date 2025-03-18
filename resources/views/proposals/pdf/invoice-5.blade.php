<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Template CSS -->
    <title>@lang('modules.lead.proposal') - {{ $proposal->proposal_number }}</title>
    @includeIf('invoices.pdf.invoice_pdf_css')
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ $company->favicon_url }}">
    <meta name="theme-color" content="#ffffff">

    @if ($invoiceSetting->locale == 'ru')
        <style>
            body {
                margin: 0;
                /*font-family: dejavu sans;*/
                font-size: 13px;
            }
        </style>
    @else
        <style>
            body {
                margin: 0;
                /* font-family: Verdana, Arial, Helvetica, sans-serif; */
                font-size: 13px;
            }
        </style>
    @endif

    <style>
        .bg-grey {
            background-color: #F2F4F7;
        }

        .bg-white {
            background-color: #fff;
        }

        .border-radius-25 {
            border-radius: 0.25rem;
        }

        .p-25 {
            padding: 1.25rem;
        }

        .f-11 {
            font-size: 11px;
        }

        .f-12 {
            font-size: 12px;
        }

        .f-13 {
            font-size: 13px;
        }

        .f-14 {
            font-size: 13px;
        }

        .f-15 {
            font-size: 13px;
        }

        .f-21 {
            font-size: 17px;
        }

        .text-black {
            color: #28313c;
        }

        .text-grey {
            color: #616e80;
        }

        .font-weight-700 {
            font-weight: 700;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        . {
            text-transform: capitalize;
        }

        .line-height {
            line-height: 20px;
        }

        .description {
            line-height: 12px;
        }

        .mt-1 {
            margin-top: 1rem;
        }

        .mb-0 {
            margin-bottom: 0px;
        }

        .b-collapse {
            border-collapse: collapse;
        }

        .heading-table-left {
            padding: 6px;
            border: 1px solid #DBDBDB;
            font-weight: bold;
            background-color: #f1f1f3;
            border-right: 0;
        }

        .heading-table-right {
            padding: 6px;
            border: 1px solid #DBDBDB;
            border-left: 0;
        }

        .unpaid {
            color: #000000;
            border: 1px solid #000000;
            position: relative;
            padding: 11px 22px;
            font-size: 14px;
            border-radius: 0.25rem;
            width: 100px;
            text-align: center;
        }

        .main-table-heading {
            border: 1px solid #DBDBDB;
            background-color: #f1f1f3;
            font-weight: 700;
        }

        .main-table-heading td {
            padding: 5px 8px;
            border: 1px solid #DBDBDB;
        }

        .main-table-items td {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
        }

        .total-box {
            border: 1px solid #e7e9eb;
            padding: 0px;
            border-bottom: 0px;
        }

        .subtotal {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
            border-right: 0;
        }

        .subtotal-amt {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
            border-right: 0;
        }

        .total {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            font-weight: 700;
            border-left: 0;
            border-right: 0;
        }

        .total-amt {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
            border-right: 0;
            font-weight: 700;
        }

        .balance {
            font-size: 14px;
            font-weight: bold;
            background-color: #f1f1f3;
        }

        .balance-left {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
            border-right: 0;
        }

        .balance-right {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-right: 0;
            border-left: 0;
        }

        .centered {
            margin: 0 auto;
        }

        .rightaligned {
            margin-right: 0;
            margin-left: auto;
        }

        .leftaligned {
            margin-left: 0;
            margin-right: auto;
        }

        .page_break {
            page-break-before: always;
        }

        #logo {
            height: 50px;
        }

        .word-break {
            max-width: 175px;
            word-wrap: break-word;
            word-break: break-all;
        }

        .summary {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            font-size: 11px;
        }

        .border-left-0 {
            border-left: 0 !important;
        }

        .border-right-0 {
            border-right: 0 !important;
        }

        .border-top-0 {
            border-top: 0 !important;
        }

        .border-bottom-0 {
            border-bottom: 0 !important;
        }

        .word-break {
            word-wrap: break-word;
            word-break: break-all;
        }

        @if($invoiceSetting->locale == 'th')

        table td {
            font-weight: bold !important;
            font-size: 20px !important;
        }

        .description {
            font-weight: bold !important;
            font-size: 16px !important;
        }
        @endif
    </style>
</head>

<body class="content-wrapper">
<table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
    <tbody>
    <!-- Table Row Start -->
    <tr>
        <td><img src="{{ $invoiceSetting->logo_url }}" alt="{{ $company->company_name }}"
                 id="logo"/></td>
        <td align="right" class="f-21 text-black font-weight-700 text-uppercase">@lang('modules.lead.proposal')
        </td>
    </tr>
    <!-- Table Row End -->
    <!-- Table Row Start -->
    <tr>
        <td>
            <p class="line-height mt-1 mb-0 f-14 text-black description">
                {{ $company->company_name }}<br>
                @if (!is_null($company))
                    {!! nl2br($company->defaultAddress->address) !!}<br>
                    {{ $company->company_phone }}
                @endif
                @if ($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                    <br>@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}
                @endif
            </p>
        </td>
        <td>
            <table class="text-black mt-1 f-13 b-collapse rightaligned">
                <tr>
                    <td class="heading-table-left">@lang('modules.lead.proposal') @lang('app.number')</td>
                    <td class="heading-table-right">{{ $proposal->proposal_number }}</td>
                </tr>
                <tr>
                    <td class="heading-table-left">@lang('modules.estimates.validTill')</td>
                    <td class="heading-table-right">{{ $proposal->valid_till->translatedFormat($company->date_format) }}
                    </td>
                </tr>
                <tr>
                    <td class="heading-table-left">@lang('app.date')</td>
                    <td class="heading-table-right">{{ $proposal->created_at->translatedFormat($company->date_format) }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Table Row End -->
    <!-- Table Row Start -->
    <tr>
        <td height="10"></td>
    </tr>
    <!-- Table Row End -->
    <!-- Table Row Start -->
    <tr>
        <td colspan="2">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td class="f-14 text-black">
                        @if ($proposal->lead->contact && ($proposal->lead->contact->client_name || $proposal->lead->contact->client_email || $proposal->lead->contact->mobile || $proposal->lead->contact->company_name || $proposal->lead->contact->address) && ($invoiceSetting->show_client_name == 'yes' || $invoiceSetting->show_client_email == 'yes' || $invoiceSetting->show_client_phone == 'yes' || $invoiceSetting->show_client_company_name == 'yes' || $invoiceSetting->show_client_company_address == 'yes'))
                            <p class="line-height mb-0">
                                <span class="text-grey ">@lang("modules.invoices.billedTo")</span><br>
                                @if ($proposal->deal && !empty($proposal->deal->name))
                                    {{ $proposal->deal->name }}<br>
                                @endif
                                @if ($proposal->lead->contact && $proposal->lead->contact->client_name && $invoiceSetting->show_client_name == 'yes')
                                    {{ $proposal->lead->contact->client_name_salutation }}<br>
                                @endif
                                @if ($proposal->lead->contact && $proposal->lead->contact->client_email && $invoiceSetting->show_client_email == 'yes')
                                    {{ $proposal->lead->contact->client_email }}<br>
                                @endif
                                @if ($proposal->lead->contact && $proposal->lead->contact->mobile && $invoiceSetting->show_client_phone == 'yes')
                                    {{ $proposal->lead->contact->mobile }}<br>
                                @endif
                                @if ($proposal->lead->contact && $proposal->lead->contact->company_name && $invoiceSetting->show_client_company_name == 'yes')
                                    {{ $proposal->lead->contact->company_name }}<br>
                                @endif
                                @if ($proposal->lead->contact && $proposal->lead->contact->address && $invoiceSetting->show_client_company_address == 'yes')
                                    {!! nl2br($proposal->lead->contact->address) !!}
                                @endif
                            </p>
                        @endif
                    </td>

                    <td align="right">
                        <div class="text-uppercase bg-white unpaid rightaligned">
                            @lang('modules.proposal.'. $proposal->status)</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td height="20"></td>
    </tr>
    </tbody>
</table>

@if ($proposal->description)
    <div class="f-13 description">{!! pdfStripTags($proposal->description) !!}</div>
@endif

@if (count($proposal->items) > 0)
    <table width="100%" class="f-14 b-collapse">
        <tr>
            <td height="10" colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}"></td>
        </tr>
        <!-- Table Row Start -->
        <tr class="main-table-heading text-grey">
            <td width="40%">@lang('app.description')</td>
            @if ($invoiceSetting->hsn_sac_code_show)
                <td align="right" width="10%">@lang("app.hsnSac")</td>
            @endif
            <td align="right" width="10%">@lang('modules.invoices.qty')</td>
            <td align="right">@lang("modules.invoices.unitPrice")</td>
            <td align="right">@lang("modules.invoices.tax")</td>
            <td align="right"
                width="{{ $invoiceSetting->hsn_sac_code_show ? '17%' : '20%' }}">@lang("modules.invoices.amount")
                ({{ $proposal->currency->currency_code }})
            </td>
        </tr>
        <!-- Table Row End -->
        @foreach ($proposal->items->sortBy('field_order') as $item)
            @if ($item->type == 'item')
                <!-- Table Row Start -->
                <tr class="main-table-items text-black">
                    <td width="40%" class="description word-break">{{ $item->item_name }}</td>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <td align="right" class="border-bottom-0"
                            width="10%">{{ $item->hsn_sac_code ?  : '--' }}</td>
                    @endif
                    <td align="right" class="border-bottom-0" width="10%">{{ $item->quantity }}<br><span
                            class="f-11 text-grey">{{ $item->unit->unit_type }}</td>
                    <td align="right"
                        class="border-bottom-0">{{ currency_format($item->unit_price, $proposal->currency_id, false) }}</td>
                    <td align="right" class="border-bottom-0">{{ $item->tax_list }}</td>
                    <td align="right"
                        class="border-bottom-0">{{ currency_format($item->amount, $proposal->currency_id, false) }}</td>
                </tr>
                @if ($item->item_summary != '' || $item->proposalItemImage)
                    <tr class="main-table-items text-black">
                        <td class="f-13 text-black description word-break" colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">
                            {!! nl2br(pdfStripTags($item->item_summary)) !!}
                            @if ($item->proposalItemImage)
                                <p class="mt-2 description">
                                    <img src="{{ $item->proposalItemImage->file_url }}" width="60" height="60"
                                        class="img-thumbnail">
                                </p>
                            @endif
                        </td>
                    </tr>
                @endif
            @endif
        @endforeach
    </table>

    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">

        <!-- Table Row Start -->
        <tr>
            <td class="total-box" align="right" colspan="{{ $invoiceSetting->hsn_sac_code_show ? '5' : '4' }}">
                <table width="100%" border="0" class="b-collapse">
                    <!-- Table Row Start -->
                    <tr align="right" class="text-grey">
                        <td width="50%" class="subtotal">@lang("modules.invoices.subTotal")</td>
                    </tr>
                    <!-- Table Row End -->
                    @if ($discount != 0 && $discount != '')
                        <!-- Table Row Start -->
                        <tr align="right" class="text-grey">
                            <td width="50%" class="subtotal">@lang("modules.invoices.discount"):
                                @if($proposal->discount_type == 'percent')
                                    {{$proposal->discount}}%
                                @else
                                    {{ currency_format($proposal->discount, $proposal->currency_id) }}
                                @endif
                            </td>
                        </tr>
                        <!-- Table Row End -->
                    @endif
                    @foreach ($taxes as $key => $tax)
                        <!-- Table Row Start -->
                        <tr align="right" class="text-grey">
                            <td width="50%" class="subtotal">{{ $key }}</td>
                        </tr>
                        <!-- Table Row End -->
                    @endforeach
                    <!-- Table Row Start -->
                    <tr align="right" class="balance text-black">
                        <td width="50%" class="balance-left">@lang("modules.invoices.total")</td>
                    </tr>
                    <!-- Table Row End -->

                </table>
            </td>
            <td class="total-box" align="right" width="{{ $invoiceSetting->hsn_sac_code_show ? '17%' : '20%' }}">
                <table width="100%" class="b-collapse">
                    <!-- Table Row Start -->
                    <tr align="right" class="text-grey">
                        <td class="subtotal-amt">
                            {{ currency_format($proposal->sub_total, $proposal->currency_id, false) }}</td>
                    </tr>
                    <!-- Table Row End -->
                    @if ($discount != 0 && $discount != '')
                        <!-- Table Row Start -->
                        <tr align="right" class="text-grey">
                            <td class="subtotal-amt">
                                {{ currency_format($discount, $proposal->currency_id, false) }}</td>
                        </tr>
                        <!-- Table Row End -->
                    @endif
                    @foreach ($taxes as $key => $tax)
                        <!-- Table Row Start -->
                        <tr align="right" class="text-grey">
                            <td class="subtotal-amt">{{ currency_format($tax, $proposal->currency_id, false) }}
                            </td>
                        </tr>
                        <!-- Table Row End -->
                    @endforeach
                    <!-- Table Row Start -->
                    <tr align="right" class="balance text-black">
                        <td class="balance-right">
                            {{ currency_format($proposal->total, $proposal->currency_id, false) }}
                        </td>
                    </tr>
                    <!-- Table Row End -->

                </table>
            </td>
        </tr>

        <!-- Table Row End -->
    </table>
    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tbody>
        <!-- Table Row Start -->
        <tr>
            <td height="10"></td>
        </tr>
        @if ($proposal->note != '')
            <tr>
                <td height="10"></td>
            </tr>
            <tr>
                <td class="f-11">@lang('app.note')</td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr class="text-grey">
                <td class="f-11 line-height word-break">{!! $proposal->note ? nl2br($proposal->note) : '--' !!}</td>
            </tr>
        @endif
        <!-- Table Row End -->
        @if (isset($taxes) && $invoiceSetting->tax_calculation_msg == 1)
            <!-- Table Row Start -->
            <tr class="text-grey">
                <td width="100%" class="f-11 line-height">
                    <p class="text-dark-grey">
                        @if ($proposal->calculate_tax == 'after_discount')
                            @lang('messages.calculateTaxAfterDiscount')
                        @else
                            @lang('messages.calculateTaxBeforeDiscount')
                        @endif
                    </p>
                </td>
            </tr>
            <!-- Table Row End -->
        @endif
        <!-- Table Row End -->
        <!-- Table Row Start -->
        <tr class="text-grey">
            <td colspan="2" class="f-14 line-height">
                @if ($proposal->signature)
                    @if (!is_null($proposal->signature->signature))
                        <img src="{{ $proposal->signature->signature }}" style="width: 200px;">
                        <h6>@lang('modules.estimates.signature')</h6>
                    @else
                        <h6>@lang('modules.estimates.signedBy')</h6>
                    @endif
                    <p>({{ $proposal->signature->full_name }})</p>
                @endif
            </td>
        </tr>
        <!-- Table Row End -->
        </tbody>
    </table>
    <p>
    <div style="margin-top: 10px;" class="f-11 line-height text-grey">
        <b>@lang('modules.invoiceSettings.invoiceTerms')</b><br>{!! nl2br($invoiceSetting->invoice_terms) !!}
    </div>
    </p>
    @if (isset($invoiceSetting->other_info))
        <p>
        <div style="margin-top: 10px;" class="f-11 line-height text-grey">
            {!! nl2br($invoiceSetting->other_info) !!}
        </div>
        </p>
    @endif

@endif

</body>

</html>
