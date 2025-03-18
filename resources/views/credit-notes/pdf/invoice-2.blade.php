<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@lang('app.credit-note')</title>
    @includeIf('invoices.pdf.invoice_pdf_css')
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="creditNote">

    <style>

        /* Reset styles */
        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed,
        figure, figcaption, footer, header, hgroup,
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            /* font-family: Verdana, Arial, Helvetica, sans-serif; */
            /* font-size: 80%; */
            /* vertical-align: baseline; */
        }

        html {
            line-height: 1;
        }

        ol, ul {
            list-style: none;
        }

        table {
            border-collapse: collapse;
        }

        caption, th, td {
            text-align: left;
            font-weight: normal;
            vertical-align: middle;
        }

        q, blockquote {
            quotes: none;
        }
        q:before, q:after, blockquote:before, blockquote:after {
            content: "";
            content: none;
        }

        a img {
            border: none;
        }

        article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {
            display: block;
        }

        /* Invoice styles */
        /**
         * DON'T override any styles for the <html> and <body> tags, as this may break the layout.
         * Instead wrap everything in one main <div id="container"> element where you may change
         * something like the font or the background of the invoice
         */
        html, body {
            /* MOVE ALONG, NOTHING TO CHANGE HERE! */
        }

        /**
         * IMPORTANT NOTICE: DON'T USE '!important' otherwise this may lead to broken print layout.
         * Some browsers may require '!important' in oder to work properly but be careful with it.
         */
        .clearfix {
            display: block;
            clear: both;
        }

        .hidden {
            display: none;
        }

        b, strong, .bold {
            font-weight: bold;
        }

        #container {
            font: normal 13px/1.4em 'Open Sans', Sans-serif;
            margin: 0 auto;
        }

        .invoice-top {
            color: #000000;
            padding: 40px 40px 10px 40px;
        }

        .invoice-body {
            padding: 10px 40px 40px 40px;
        }

        #memo .logo {
            float: left;
            margin-right: 20px;
        }
        #memo .logo img {
            height: 50px;
        }
        #memo .company-info {
            /*float: right;*/
            text-align: right;
        }
        #memo .company-info .company-name {
            font-size: 20px;
            text-align: right;
        }
        #memo .company-info .spacer {
            height: 15px;
            display: block;
        }
        #memo .company-info div {
            font-size: 12px;
            text-align: right;
            line-height: 18px;
        }
        #memo:after {
            content: '';
            display: block;
            clear: both;
        }

        #invoice-info {
            text-align: left;
            margin-top: 20px;
            line-height: 18px;
        }

        #invoice-info table{
            width: 30%;
        }
        #invoice-info > div {
            float: left;
        }
        #invoice-info > div > span {
            display: block;
            min-width: 100px;
            min-height: 18px;
            margin-bottom: 3px;
        }

        #invoice-info:after {
            content: '';
            display: block;
            clear: both;
        }

        #client-info {
            text-align: right;
            min-width: 220px;
            line-height: 18px;
        }
        #client-info > div {
            margin-bottom: 3px;
        }
        #client-info span {
            display: block;
        }
        #client-info > span {
            margin-bottom: 3px;
        }

        #invoice-title-number {
            margin-top: 30px;
        }
        #invoice-title-number #title {
            font-size: 35px;
        }
        #invoice-title-number #number {
            text-align: left;
            font-size: 20px;
        }

        table {
            table-layout: fixed;
        }
        table th, table td {
            vertical-align: top;
            word-break: keep-all;
            word-wrap: break-word;
        }

        #items .first-cell, #items table th:first-child, #items table td:first-child {
            width: 18px;
            text-align: right;
        }
        #items table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #000000
        }
        #items table th {
            font-weight: bold;
            padding: 12px 10px;
            text-align: right;
            border-bottom: 1px solid #444;
        }
        #items table th:nth-child(2) {
            width: 30%;
            text-align: left;
        }
        #items table th:last-child {
            text-align: right;
        }
        #items table td {
            border-right: 1px solid #b6b6b6;
            padding: 7px 10px;
            text-align: right;
        }
        #items table td:first-child {
            text-align: left;
        }
        #items table td:nth-child(2) {
            text-align: left;
        }
        #items table td:last-child {
            border-right: none !important;
        }

        #terms > div {
            min-height: 30px;
        }

        .payment-info {
            color: #707070;
            font-size: 12px;
        }
        .payment-info div {
            display: inline-block;
            min-width: 10px;
        }

        .ib_drop_zone {
            color: #F8ED31 !important;
            border-color: #F8ED31 !important;
        }

        .item-summary{
            font-size: 11px;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        /**
         * If the printed invoice is not looking as expected you may tune up
         * the print styles (you can use !important to override styles)
         */
        @media print {
            /* Here goes your print styles */
        }

        .page_break { page-break-before: always; }

        .h3-border {
            border-bottom: 1px solid #AAAAAA;
        }
        table td.text-center
        {
            text-align: center;
        }
        table td.text-right
        {
            text-align: right;
        }

        #itemsPayment .first-cell, #itemsPayment table th:first-child, #itemsPayment table td:first-child {
            width: 18px;
            text-align: right;
        }
        #itemsPayment table {
            border-collapse: separate;
            width: 100%;
        }
        #itemsPayment table th {
            font-weight: bold;
            padding: 12px 10px;
            text-align: right;
            border-bottom: 1px solid #444;
            text-transform: uppercase;
        }
        #itemsPayment table th:nth-child(2) {
            width: 30%;
            text-align: left;
        }
        #itemsPayment table th:last-child {
            text-align: right;
        }
        #itemsPayment table td {
            border-right: 1px solid #b6b6b6;
            padding: 15px 10px;
            text-align: right;
        }
        #itemsPayment table td:first-child {
            text-align: left;
            /*border-right: none !important;*/
        }
        #itemsPayment table td:nth-child(2) {
            text-align: left;
        }
        #itemsPayment table td:last-child {
            border-right: none !important;
        }

        .word-break {
            word-wrap:break-word;
            word-break: break-all;
        }
        @if($invoiceSetting->locale == 'th')

        section, div, span, h1, h2, h3, h4, h5, p, .description, table th, table td
            {
                font-weight: bold !important;
                font-size: 16px !important;
            }
        @endif

    </style>
</head>
<body>
<div id="container">
    <div class="invoice-top">
        <section id="memo">
            <div class="logo">
                <img src="{{ invoice_setting()->logo_url }}" />
            </div>

            <div class="company-info">
                <span class="company-name">
                    {{ company()->company_name }}
                </span>

                <span class="spacer"></span>

                <div>{!! nl2br(default_address()->address) !!}</div>


                <span class="clearfix"></span>

                <div>{{ company()->company_phone }}</div>

                <span class="clearfix"></span>

                @if($creditNoteSetting->show_gst == 'yes' && !is_null($creditNoteSetting->gst_number))
                    <div>@lang('app.gstIn'): {{ $creditNoteSetting->gst_number }}</div>
                @endif
            </div>

        </section>

        <section id="invoice-info">
            <table>
                <tr>
                    <td>@lang('app.issuesDate'):</td>
                    <td>{{ $creditNote->issue_date->translatedFormat(company()->date_format) }}</td>
                </tr>
                @if($invoiceNumber)
                    <tr>
                        <td>@lang('app.invoiceNumber'):</td>
                        <td>{{ $invoiceNumber->invoice_number }}</td>
                    </tr>
                @endif
                <tr>
                    <td>@lang('app.status'):</td>
                    <td>{{ $creditNote->status }}</td>
                </tr>
            </table>


            <div class="clearfix"></div>

            <div id="invoice-title-number">

                <span id="number">{{ $creditNote->cn_number }}</span></p>

            </div>
        </section>
        @if(!is_null($creditNote->project) && !is_null($creditNote->project->client))
            <section id="client-info">
                @if(!is_null($creditNote->project->client))
                    <span>@lang('modules.credit-notes.billedTo'):</span>
                    <div>
                        <span class="bold">{{ $creditNote->project->client->name_salutation }}</span>
                    </div>

                    <div>
                        <span>{{ $creditNote->project->clientDetails->company_name }}</span>
                    </div>

                    <div>
                        <span>{{ $creditNote->project->client->email }}</span>
                    </div>

                    <div>
                        <b>@lang('app.address') :</b>
                        <span>{!! nl2br($creditNote->project->clientDetails->address) !!}</span>
                    </div>

                    @if($creditNoteSetting->show_gst == 'yes' && !is_null($creditNote->project->clientDetails->gst_number))
                        <div>
                            <span> @lang('app.gstIn'): {{ $creditNote->project->clientDetails->gst_number }} </span>
                        </div>
                    @endif
                @endif
                @if (($invoiceSetting->show_project == 1) && (isset($creditNote->project)))
                <br>
                <span class="text-dark-grey ">@lang("modules.invoices.projectName"):</span>
                {{ $creditNote->project->project_name }}
                @endif
            </section>
        @elseif(is_null($creditNote->project) && !is_null($creditNote->client->clientDetails))
            <section id="client-info">
                @if(!is_null($creditNote->client))
                    <span>@lang('modules.credit-notes.billedTo'):</span>
                    <div>
                        <span class="bold">{{ $creditNote->client->name_salutation }}</span>
                    </div>

                    <div>
                        <span>{{ $creditNote->client->clientDetails->company_name }}</span>
                    </div>

                    <div>
                        <span>{{ $creditNote->client->email }}</span>
                    </div>

                    <div>
                        <b>@lang('app.address') :</b>
                        <span>{!! nl2br($creditNote->client->clientDetails->address) !!}</span>
                    </div>
                    @if($creditNoteSetting->show_gst == 'yes' && !is_null($creditNote->client->clientDetails->gst_number))
                        <div>
                            <span> @lang('app.gstIn'): {{ $creditNote->client->clientDetails->gst_number }} </span>
                        </div>
                    @endif
                @endif
                @if (($invoiceSetting->show_project == 1) && (isset($creditNote->client)))
                    <br>
                    <span class="text-dark-grey ">@lang("modules.invoices.projectName"):</span>
                    {{ $creditNote->project->project_name }}
                @endif
            </section>
        @endif
        <div class="clearfix"></div>
    </div>

    <div class="invoice-body">
        <section id="items">

            <table cellpadding="0" cellspacing="0">

                <tr>
                    <th>#</th> <!-- Dummy cell for the row number and row commands -->
                    <th>@lang("modules.credit-notes.item")</th>
                    @if($invoiceSetting->hsn_sac_code_show)
                        <th>@lang("app.hsnSac")</th>
                    @endif
                    <th>@lang('modules.invoices.qty')</th>
                    <th>@lang("modules.credit-notes.unitPrice")</th>
                    <th>@lang("modules.invoices.tax")</th>
                    <th>@lang("modules.credit-notes.price") ({!! htmlentities($creditNote->currency->currency_code)  !!})</th>
                </tr>

                <?php $count = 0; ?>
                @foreach($creditNote->items as $item)
                    @if($item->type == 'item')
                <tr data-iterate="item">
                    <td>{{ ++$count }}</td> <!-- Don't remove this column as it's needed for the row commands -->
                    <td class="word-break">
                        {{ $item->item_name }}
                        @if (!is_null($item->item_summary))
                            <p class="item-summary word-break">{!! nl2br(pdfStripTags($item->item_summary)) !!}</p>
                        @endif
                        @if ($item->creditNoteItemImage)
                            <p class="mt-2">
                                <img src="{{ $item->creditNoteItemImage->file_url }}" width="60" height="60" class="img-thumbnail">
                            </p>
                        @endif
                    </td>
                    @if($invoiceSetting->hsn_sac_code_show)
                        <td>{{ $item->hsn_sac_code ? $item->hsn_sac_code : '--' }}</td>
                    @endif
                    <td>{{ $item->quantity }}@if($item->unit)<br><span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>@endif</td>
                    <td>{{ currency_format($item->unit_price, $creditNote->currency_id, false) }}</td>
                    <td>{{ $item->tax_list }}</td>
                    <td>{{ currency_format($item->amount, $creditNote->currency_id, false) }}</td>
                </tr>
                    @endif
                @endforeach

            </table>

            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="{{ $creditNoteSetting->hsn_sac_code_show ? '5': '4' }}">@lang("modules.credit-notes.subTotal"):</td>
                    <td>{{ currency_format($creditNote->sub_total, $creditNote->currency_id, false) }}</td>
                </tr>
                @if($discount != 0 && $discount != '')
                <tr data-iterate="tax">
                    <td colspan="{{ $creditNoteSetting->hsn_sac_code_show ? '5': '4' }}">@lang("modules.credit-notes.discount"):
                        @if($creditNote->discount_type == 'percent')
                                {{$creditNote->discount}}%
                            @else
                                {{ currency_format($creditNote->discount, $creditNote->currency_id) }}
                            @endif</td>
                    <td>-{{ currency_format($discount, $creditNote->currency_id, false) }}</td>
                </tr>
                @endif
                @foreach($taxes as $key=>$tax)
                <tr data-iterate="tax">
                    <td colspan="{{ $creditNoteSetting->hsn_sac_code_show ? '5': '4' }}">{{ $key }}:</td>
                    <td>{{ currency_format($tax, $creditNote->currency_id, false) }}</td>
                </tr>
                @endforeach

                <tr class="amount-total">
                    <td colspan="{{ $creditNoteSetting->hsn_sac_code_show ? '5': '4' }}">@lang("modules.credit-notes.total"):</td>
                    <td>{{ currency_format($creditNote->total, $creditNote->currency_id, false) }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $creditNoteSetting->hsn_sac_code_show ? '5': '4' }}">@lang("modules.credit-notes.creditAmountUsed"):</td>
                    <td>{{ currency_format($creditNote->creditAmountUsed(), $creditNote->currency_id, false) }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $creditNoteSetting->hsn_sac_code_show ? '5': '4' }}">@lang('app.adjustmentAmount'):</td>
                    <td>{{ currency_format($creditNote->adjustment_amount, $creditNote->currency_id, false) }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $creditNoteSetting->hsn_sac_code_show ? '5': '4' }}">
                        @lang("modules.credit-notes.creditAmountRemaining"):</td>
                    <td>
                        {{ currency_format($creditNote->creditAmountRemaining(), $creditNote->currency_id, false) }}</td>
                </tr>


            </table>

        </section>
        <section id="terms">
            @if(!is_null($creditNote->note))
                <div class="word-break item-summary">{!! nl2br($creditNote->note) !!}</div>
            @endif

            <div class="word-break item-summary">{!! nl2br($creditNoteSetting->invoice_terms) !!}</div>

            @if (isset($invoiceSetting->other_info))
                <div class="word-break item-summary description">
                    {!! nl2br($invoiceSetting->other_info) !!}
                </div>
            @endif
        </section>


    </div>

</div>

</body>
</html>
