<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@lang('app.order') - {{ $order->order_number }}</title>
    @includeIf('invoices.pdf.invoice_pdf_css')
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ global_setting()->favicon_url }}">
    <meta name="theme-color" content="#ffffff">

    @if (invoice_setting()->locale == 'ru')
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
                /*font-family: Verdana, Arial, Helvetica, sans-serif;*/
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
            width: 120px;
            text-align: center;
            margin-top: 50px;
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
            border-left: 0;
            border-right: 0;
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

        .note-text {
            max-width:175px;
        }

        .word-break {
            word-wrap:break-word;
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


    </style>
</head>

<body class="content-wrapper">
    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tbody>
            <!-- Table Row Start -->
            <tr>
                <td><img src="{{ invoice_setting()->logo_url }}" alt="{{ company()->company_name }}"
                        id="logo" /></td>
                <td align="right" class="f-21 text-black font-weight-700 text-uppercase">@lang('app.order')</td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td>
                    <p class="line-height mt-1 mb-0 f-14 text-black">
                        {{ company()->company_name }}<br>
                        @if (!is_null($settings) && $order->address)
                            {!! nl2br($order->address->address) !!}<br>
                        @endif
                        {{ company()->company_phone }}<br>
                        @if ($invoiceSetting->show_gst == 'yes' && $order->address)
                            <br>{{ $order->address->tax_name }}: {{ $order->address->tax_number }}
                        @endif
                    </p>
                </td>
                <td>
                    <table class="text-black mt-1 f-13 b-collapse rightaligned">
                        <tr>
                            <td class="heading-table-left">@lang('modules.orders.orderNumber')</td>
                            <td class="heading-table-right">{{ $order->order_number }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('modules.orders.orderDate')</td>
                            <td class="heading-table-right">{{ \Carbon\Carbon::parse($order->order_date)->translatedFormat(company()->date_format) }}
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
                    @php
                        $client = $order->client;
                    @endphp

                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        @if ($order->project)
                            <tr>
                                <td class="f-14 text-black">
                                    <p class="line-height mb-0">
                                        <span class="text-grey ">
                                            @lang('modules.invoices.project')</span><br>
                                        @if ($order->project)
                                        {{$order->project->project_name}}
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="f-14 text-black">
                                @if (($invoiceSetting->show_client_name == 'yes' || $invoiceSetting->show_client_email == 'yes' || $invoiceSetting->show_client_phone == 'yes' || $invoiceSetting->show_client_company_name == 'yes' || $invoiceSetting->show_client_company_address == 'yes') && $client)
                                    <p class="line-height mb-0">
                                        <span class="text-grey ">
                                            @lang("modules.invoices.billedTo")</span><br>

                                            @if ($client->name && $invoiceSetting->show_client_name == 'yes')
                                                {{ $client->name_salutation }}<br>
                                            @endif

                                            @if ($client->email && $invoiceSetting->show_client_email == 'yes')
                                                {{ $client->email }}<br>
                                            @endif

                                            @if ($client->mobile && $invoiceSetting->show_client_phone == 'yes')
                                                {{ $client->mobile_with_phonecode }}<br>
                                            @endif

                                            @if ($client->clientDetails->company_name && $invoiceSetting->show_client_company_name == 'yes')
                                                {{ $client->clientDetails->company_name }}<br>
                                            @endif

                                            @if ($client->clientDetails->address && $invoiceSetting->show_client_company_address == 'yes')
                                                {!! nl2br($client->clientDetails->address) !!}
                                            @endif
                                    </p>
                                @endif

                                @if ($invoiceSetting->show_gst == 'yes' && !is_null($client->clientDetails->gst_number))
                                    <br>@lang('app.gstIn'):
                                    {{ $client->clientDetails->gst_number }}
                                @endif
                            </td>
                            <td class="f-14 text-black">
                                @if ($order->show_shipping_address == 'yes' && $client->clientDetails->shipping_address && $invoiceSetting->show_client_company_address == 'yes')
                                    <p class="line-height"><span
                                            class="text-grey ">@lang('app.shippingAddress')</span><br>
                                        {!! nl2br($client->clientDetails->shipping_address) !!}</p>
                                @endif
                            </td>
                            <td align="right">
                                <br />
                                <div class="text-uppercase bg-white unpaid rightaligned">
                                    @lang('modules.invoices.'.$order->status)
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <table width="100%" class="f-14 b-collapse">
        <tr>
            <td height="10" colspan="{{ $invoiceSetting->hsn_sac_code_show ? '7' : '6' }}"></td>
        </tr>
        <!-- Table Row Start -->
        <tr class="main-table-heading text-grey">
            <td width="40%">@lang('app.description')</td>
            @if($invoiceSetting->hsn_sac_code_show)
                <td align="right">@lang("app.hsnSac")</td>
            @endif
            <th class="qty">@lang('modules.invoices.qty')</th>
            s<td align="right">@lang('app.sku')</td>
            <td align="right">@lang("modules.invoices.unitPrice")</td>
            <td align="right">@lang("modules.invoices.tax")</td>
            <td align="right" width="{{ $invoiceSetting->hsn_sac_code_show ? '17%' : '20%' }}">@lang("modules.invoices.amount")
                ({{ $order->currency->currency_code }})</td>
        </tr>
        <!-- Table Row End -->
        @foreach ($order->items->sortBy('field_order') as $item)
            @if ($item->type == 'item')
                <!-- Table Row Start -->
                <tr class="main-table-items text-black">
                    <td width="40%" class="border-bottom-0 word-break">
                        {{ $item->item_name }}
                    </td>
                    @if($invoiceSetting->hsn_sac_code_show)
                        <td align="right" width="10%" class="border-bottom-0">{{ $item->hsn_sac_code ? $item->hsn_sac_code : '--' }}</td>
                    @endif
                    <td align="right" width="10%" class="border-bottom-0">{{ $item->quantity }}@if($item->unit)<br><span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>@endif</td>
                    <td align="right" width="10%" class="border-bottom-0"><span class="f-11 text-grey">{{ $item->sku }}</td>
                    <td align="right" class="border-bottom-0">{{ currency_format($item->unit_price, $order->currency_id, false) }}</td>
                    <td align="right" class="border-bottom-0">{{ $item->tax_list }}</td>
                    <td align="right" class="border-bottom-0" width="{{ $invoiceSetting->hsn_sac_code_show ? '17%' : '20%' }}">{{ currency_format($item->amount, $order->currency_id, false) }}</td>
                </tr>
                <!-- Table Row End -->
                @if ($item->item_summary != '' || $item->orderItemImage)
                {{-- DOMPDF HACK FOR RENDER IN TABLE --}}
                </table>
                    <div class="f-13 summary text-black border-bottom-0 word-break">
                        {!! nl2br(pdfStripTags($item->item_summary)) !!}
                        @if ($item->orderItemImage)
                            <p class="mt-2">
                                <img src="{{ $item->orderItemImage->file_url }}" width="60" height="60"
                                    class="img-thumbnail">
                            </p>
                        @endif
                    </div>
                {{-- DOMPDF HACK FOR RENDER IN TABLE --}}
                <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                @endif
            @endif
        @endforeach
        <!-- Table Row Start -->
        <tr>
            <td class="total-box" align="right" colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">
                <table width="100%" border="0" class="b-collapse">
                    <!-- Table Row Start -->
                    <tr align="right" class="text-grey">
                        <td width="50%" class="subtotal">@lang("modules.invoices.subTotal")</td>
                    </tr>
                    <!-- Table Row End -->
                    @if ($discount != 0 && $discount != '')
                        <!-- Table Row Start -->
                        <tr align="right" class="text-grey">
                            <td width="50%" class="subtotal">@lang("modules.invoices.discount")
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
                            {{ currency_format($order->sub_total, $order->currency_id, false) }}</td>
                    </tr>
                    <!-- Table Row End -->
                    @if ($discount != 0 && $discount != '')
                        <!-- Table Row Start -->
                        <tr align="right" class="text-grey">
                            <td class="subtotal-amt">
                                {{ currency_format($discount, $order->currency_id, false) }}</td>
                        </tr>
                        <!-- Table Row End -->
                    @endif
                    @foreach ($taxes as $key => $tax)
                        <!-- Table Row Start -->
                        <tr align="right" class="text-grey">
                            <td class="subtotal-amt">{{ currency_format($tax, $order->currency_id, false) }}
                            </td>
                        </tr>
                        <!-- Table Row End -->
                    @endforeach
                    <!-- Table Row Start -->
                    <tr align="right" class="balance text-black">
                        <td class="balance-right">
                            {{ currency_format($order->total, $order->currency_id, false) }}</td>
                    </tr>
                    <!-- Table Row End -->
                </table>
            </td>
        </tr>
    </table>
    @if ($order->note != '')
    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tbody>
            <!-- Table Row Start -->
                <tr>
                    <td height="10">&nbsp;</td>
                </tr>
                <tr>
                    <td class="f-11">@lang('app.note')</td>
                </tr>
                <!-- Table Row End -->
                <!-- Table Row Start -->
                <tr class="text-grey">
                    <td class="f-11 line-height word-break note-text">{!! $order->note ? nl2br($order->note) : '--' !!}</td>
                </tr>
                @if ($invoiceSetting->other_info)
                    <tr class="text-grey">
                        <td class="f-11 line-height word-break note-text">
                            <br>{!! nl2br($invoiceSetting->other_info) !!}
                        </td>
                    </tr>
                @endif

            <!-- Table Row End -->
        </tbody>
    </table>
    @endif
</body>

</html>
