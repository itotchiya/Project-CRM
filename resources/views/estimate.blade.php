<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}">

    <!-- Simple Line Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/simple-line-icons.css') }}">

    <!-- Template CSS -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">

    <title>@lang($pageTitle)</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ $company->favicon_url }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $company->favicon_url }}">

    @include('sections.theme_css', ['company' => $company])

    @isset($activeSettingMenu)
        <style>
            .preloader-container {
                margin-left: 510px;
                width: calc(100% - 510px)
            }

        </style>
    @endisset

    @stack('styles')

    <style>
        :root {
            --fc-border-color: #E8EEF3;
            --fc-button-text-color: #99A5B5;
            --fc-button-border-color: #99A5B5;
            --fc-button-bg-color: #ffffff;
            --fc-button-active-bg-color: #171f29;
            --fc-today-bg-color: #f2f4f7;
        }

        .preloader-container {
            height: 100vh;
            width: 100%;
            margin-left: 0;
            margin-top: 0;
        }

        .rtl .preloader-container {
            margin-right: 0;
        }

        .fc a[data-navlink] {
            color: #99a5b5;
        }

    </style>
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

        .logo {
            height: 50px;
        }

    </style>


    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/modernizr.min.js') }}"></script>

    <script>
        var checkMiniSidebar = localStorage.getItem("mini-sidebar");
    </script>

</head>

<body id="body" class="h-100 bg-additional-grey {{ isRtl('rtl') }}">

<!-- BODY WRAPPER START -->
<div class="clearfix body-wrapper">


    <!-- MAIN CONTAINER START -->
    <section class="bg-additional-grey" id="fullscreen">

        <div class="preloader-container d-flex justify-content-center align-items-center">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
        </div>

        <x-app-title class="d-block d-lg-none" :pageTitle="$pageTitle"></x-app-title>

        <div class="container content-wrapper">

            <!-- INVOICE CARD START -->
            <div class="border-0 card invoice">
                <!-- CARD BODY START -->
                <div class="card-body">
                    <div class="invoice-table-wrapper">
                        <table width="100%" class="">
                            <tr class="inv-logo-heading">
                                <td><img src="{{ $invoiceSetting->logo_url }}"
                                         alt="{{ $company->company_name }}" id="logo"/></td>
                                <td align="right"
                                    class="mt-4 font-weight-bold f-21 text-dark text-uppercase mt-lg-0 mt-md-0">
                                    @lang('app.estimate')</td>
                            </tr>
                            <tr class="inv-num">
                                <td class="f-14 text-dark">
                                    <p class="mt-3 mb-0">
                                        {{ $company->company_name }}<br>
                                        @if (!is_null($company))
                                            {!! nl2br($defaultAddress->address) !!}<br>
                                            {{ $company->company_phone }}
                                        @endif
                                        @if ($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                                            <br><br>{{ $invoiceSetting->tax_name }}: {{ $invoiceSetting->gst_number }}<br>
                                        @endif
                                    </p><br>
                                </td>
                                <td align="right">
                                    <table class="mt-3 inv-num-date text-dark f-13">
                                        <tr>
                                            <td class="bg-light-grey border-right-0 f-w-500">
                                                @lang('modules.estimates.estimatesNumber')</td>
                                            <td class="border-left-0">{{ $estimate->estimate_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light-grey border-right-0 f-w-500">
                                                @lang('modules.estimates.validTill')</td>
                                            <td class="border-left-0">
                                                {{ $estimate->valid_till->translatedFormat($company->date_format) }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="20"></td>
                            </tr>
                        </table>
                        <table width="100%">
                            <tr class="inv-unpaid">
                                <td class="f-14 text-dark">
                                    @if (($estimate->client || $estimate->clientDetails) && ($estimate->client->name || $estimate->client->email || $estimate->client->mobile || $estimate->clientDetails->company_name || $estimate->clientDetails->address) && ($invoiceSetting->show_client_name == 'yes' || $invoiceSetting->show_client_email == 'yes' || $invoiceSetting->show_client_phone == 'yes' || $invoiceSetting->show_client_company_name == 'yes' || $invoiceSetting->show_client_company_address == 'yes'))
                                        <p class="mb-0 text-left">
                                            <span class="text-dark-grey ">
                                                @lang("modules.invoices.billedTo")
                                            </span><br>

                                            @if ($estimate->client && $estimate->client->name && $invoiceSetting->show_client_name == 'yes')
                                                {{ $estimate->client->name_salutation }}<br>
                                            @endif
                                            @if ($estimate->client && $estimate->client->email && $invoiceSetting->show_client_email == 'yes')
                                                {{ $estimate->client->email }}<br>
                                            @endif
                                            @if ($estimate->client && $estimate->client->mobile && $invoiceSetting->show_client_phone == 'yes')
                                                {{ $estimate->client->mobile_with_phonecode }}
                                                <br>
                                            @endif
                                            @if ($estimate->clientDetails && $estimate->clientDetails->company_name && $invoiceSetting->show_client_company_name == 'yes')
                                                {{ $estimate->clientDetails->company_name }}<br>
                                            @endif
                                            @if ($estimate->clientDetails && $estimate->clientDetails->address && $invoiceSetting->show_client_company_address == 'yes')
                                                {!! nl2br($estimate->clientDetails->address) !!}<br><br>
                                            @endif
                                            @if ($estimate->clientDetails && $estimate->clientDetails->gst_number && invoice_setting()->show_gst == 'yes')
                                                {{ $estimate->clientDetails->tax_name }}: {{ $estimate->clientDetails->gst_number }}<br>
                                            @endif
                                        </p>
                                    @endif
                                </td>

                                <td align="right" class="mt-4 mt-lg-0 mt-md-0">
                                    @if ($estimate->clientDetails->company_logo)
                                        <img src="{{ $estimate->clientDetails->image_url }}"
                                             alt="{{ $estimate->clientDetails->company_name }}" class="logo"/>
                                        <br><br><br>
                                    @endif
                                    <span
                                        class="unpaid {{ $estimate->status == 'draft' ? 'text-primary border-primary' : '' }} {{ $estimate->status == 'accepted' ? 'text-success border-success' : '' }} rounded f-15 ">@lang('modules.estimates.'.$estimate->status)</span>
                                </td>
                            </tr>
                            <tr>
                                <td height="30" colspan="2"></td>
                            </tr>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 ql-editor">
                                {!! $estimate->description !!}
                            </div>
                        </div>
                        <table width="100%" class="inv-desc d-none d-lg-table d-md-table">
                            <tr>
                                <td colspan="2">
                                    <table class="inv-detail f-14 table-responsive-sm" width="100%">
                                        <tr class="i-d-heading bg-light-grey text-dark-grey font-weight-bold">
                                            <td class="border-right-0">@lang('app.description')</td>
                                            @if ($invoiceSetting->hsn_sac_code_show)
                                                <td class="border-right-0 border-left-0" align="right">
                                                    @lang("app.hsnSac")</td>
                                            @endif
                                            <td class="border-right-0 border-left-0"
                                                align="right">@lang('modules.invoices.qty')</td>
                                            <td class="border-right-0 border-left-0" align="right">
                                                @lang("modules.invoices.unitPrice")
                                                ({{ $estimate->currency->currency_code }})
                                            </td>
                                            <td class="border-left-0" align="right">@lang("modules.invoices.tax")</td>
                                            <td class="border-left-0" align="right">
                                                @lang("modules.invoices.amount")
                                                ({{ $estimate->currency->currency_code }})
                                            </td>
                                        </tr>
                                        @foreach ($estimate->items->sortBy('field_order') as $item)
                                            @if ($item->type == 'item')
                                                <tr class="text-dark font-weight-semibold f-13">
                                                    <td>{{ $item->item_name }}</td>
                                                    @if ($invoiceSetting->hsn_sac_code_show)
                                                        <td align="right">{{ $item->hsn_sac_code }}</td>
                                                    @endif
                                                    <td align="right">{{ $item->quantity }}@if($item->unit)
                                                            <br><span
                                                                class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>
                                                        @endif</td>
                                                    <td align="right">
                                                        {{ currency_format($item->unit_price, $estimate->currency_id, false) }}
                                                    </td>
                                                    <td align="right">{{ $item->tax_list }}</td>
                                                    <td align="right">
                                                        {{ currency_format($item->amount, $estimate->currency_id, false) }}
                                                    </td>
                                                </tr>

                                                @if ($item->item_summary || $item->estimateItemImage)
                                                    <tr class="text-dark f-12">
                                                        <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}"
                                                            class="border-bottom-0">
                                                            {!! nl2br(strip_tags($item->item_summary)) !!}
                                                            @if ($item->estimateItemImage)
                                                                <p class="mt-2">
                                                                    <a href="javascript:;" class="img-lightbox"
                                                                       data-image-url="{{ $item->estimateItemImage->file_url }}">
                                                                        <img
                                                                            src="{{ $item->estimateItemImage->file_url }}"
                                                                            width="80" height="80"
                                                                            class="img-thumbnail">
                                                                    </a>
                                                                </p>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif
                                        @endforeach

                                        <tr>
                                            <td colspan="3"
                                                class="blank-td border-bottom-0 border-left-0 border-right-0"></td>
                                            <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? 4 : 3 }}"
                                                class="p-0 ">
                                                <table width="100%">
                                                    <tr class="text-dark-grey" align="right">
                                                        <td class="border-top-0 border-left-0">
                                                            @lang("modules.invoices.subTotal")</td>
                                                        <td class="border-top-0 border-right-0">
                                                            {{ currency_format($estimate->sub_total, $estimate->currency_id, false) }}
                                                        </td>
                                                    </tr>
                                                    @if ($discount != 0 && $discount != '')
                                                        <tr class="text-dark-grey" align="right">
                                                            <td class="border-top-0 border-left-0">
                                                                @lang("modules.invoices.discount")</td>
                                                            <td class="border-top-0 border-right-0">
                                                                {{ currency_format($discount, $estimate->currency_id, false) }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    @foreach ($taxes as $key => $tax)
                                                        <tr class="text-dark-grey" align="right">
                                                            <td class="border-top-0 border-left-0">
                                                                {{ $key }}</td>
                                                            <td class="border-top-0 border-right-0">
                                                                {{ currency_format($tax, $estimate->currency_id, false) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr class=" text-dark-grey font-weight-bold" align="right">
                                                        <td class="border-bottom-0 border-left-0">
                                                            @lang("modules.invoices.total")</td>
                                                        <td class="border-bottom-0 border-right-0">
                                                            {{ currency_format($estimate->total, $estimate->currency_id, false) }}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                            </tr>
                        </table>
                        <table width="100%" class="inv-desc-mob d-block d-lg-none d-md-none">

                            @foreach ($estimate->items->sortBy('field_order') as $item)
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
                                                @if ($item->item_summary != '' || $item->estimateItemImage)
                                                    <tr>
                                                        <td class="border-left-0 border-right-0 border-bottom-0 f-12">
                                                            {!! nl2br(strip_tags($item->item_summary)) !!}
                                                            @if ($item->estimateItemImage)
                                                                <p class="mt-2">
                                                                    <a href="javascript:;" class="img-lightbox"
                                                                       data-image-url="{{ $item->estimateItemImage->file_url }}">
                                                                        <img
                                                                            src="{{ $item->estimateItemImage->file_url }}"
                                                                            width="80" height="80"
                                                                            class="img-thumbnail">
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
                                        <td width="50%">{{ $item->quantity }}</td>
                                    </tr>
                                    <tr>
                                        <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                            @lang("modules.invoices.unitPrice")
                                            ({{ $estimate->currency->currency_code }})
                                        </th>
                                        <td width="50%">
                                            {{ currency_format($item->unit_price, $estimate->currency_id, false) }}</td>
                                    </tr>
                                    <tr>
                                        <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                            @lang("modules.invoices.amount")
                                            ({{ $estimate->currency->currency_code }})
                                        </th>
                                        <td width="50%">{{ currency_format($item->amount, $estimate->currency_id, false) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="3" class="p-0 " colspan="2"></td>
                                    </tr>
                                @endif
                            @endforeach

                            <tr>
                                <th width="50%" class="text-dark-grey font-weight-normal">
                                    @lang("modules.invoices.subTotal")
                                </th>
                                <td width="50%" class="text-dark-grey font-weight-normal">
                                    {{ currency_format($estimate->sub_total, $estimate->currency_id, false) }}</td>
                            </tr>
                            @if ($discount != 0 && $discount != '')
                                <tr>
                                    <th width="50%" class="text-dark-grey font-weight-normal">
                                        @lang("modules.invoices.discount")
                                    </th>
                                    <td width="50%" class="text-dark-grey font-weight-normal">
                                        {{ currency_format($discount, $estimate->currency_id, false) }}</td>
                                </tr>
                            @endif

                            @foreach ($taxes as $key => $tax)
                                <tr>
                                    <th width="50%" class="text-dark-grey font-weight-normal">
                                        {{ $key }}</th>
                                    <td width="50%" class="text-dark-grey font-weight-normal">
                                        {{ currency_format($tax, $estimate->currency_id, false) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <th width="50%" class="text-dark-grey font-weight-bold">
                                    @lang("modules.invoices.total")</th>
                                <td width="50%" class="text-dark-grey font-weight-bold">
                                    {{ currency_format($estimate->total, $estimate->currency_id, false) }}</td>
                            </tr>
                        </table>
                        <table class="inv-note">
                            <tr>
                                <td height="30" colspan="2"></td>
                            </tr>
                            <tr>
                                <td style="vertical-align: text-top">
                                    <table>
                                        <tr>@lang('app.note')</tr>
                                        <tr>
                                            <p class="text-dark-grey">{!! $estimate->note ?? '--' !!}</p>
                                        </tr>
                                    </table>
                                </td>
                                <td align="right">
                                    <table>
                                        <tr>@lang('modules.invoiceSettings.invoiceTerms')</tr>
                                        <tr>
                                            <p class="text-dark-grey">{!! nl2br($invoiceSetting->invoice_terms) !!}</p>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            @if (isset($invoiceSetting->other_info))
                                <tr>
                                    <td align="vertical-align: text-top">
                                        <table>
                                            <tr>
                                                <p class="text-dark-grey">{!! nl2br($invoiceSetting->other_info) !!}
                                                </p>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endif
                            @if (isset($taxes) && $invoiceSetting->tax_calculation_msg == 1)
                                <tr>
                                    <td>
                                        <p class="text-dark-grey">
                                            @if ($estimate->calculate_tax == 'after_discount')
                                                @lang('messages.calculateTaxAfterDiscount')
                                            @else
                                                @lang('messages.calculateTaxBeforeDiscount')
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>

                    @if ($estimate->sign)
                        <div class="row">
                            <div class="mt-4 col-sm-12">
                                <h6>@lang('modules.estimates.signature')</h6>
                                <img src="{{ $estimate->sign->signature }}" style="width: 200px;">
                                <p>({{ $estimate->sign->full_name }})</p>
                            </div>
                        </div>
                    @endif

                </div>
                <!-- CARD BODY END -->
                <!-- CARD FOOTER START -->
                <div
                    class="py-0 mb-4 bg-white border-0 card-footer d-flex justify-content-end py-lg-4 py-md-4 mb-lg-3 mb-md-3 ">

                    <div class="d-flex">

                        <x-forms.button-cancel :link="route('estimates.index')" class="mr-3 border-0">
                            @lang('app.cancel')
                        </x-forms.button-cancel>

                        <x-forms.link-secondary :link="route('front.estimate.download', [$estimate->hash])" class="mr-3"
                                                icon="download">@lang('app.download')
                        </x-forms.link-secondary>

                        @if ($estimate->status == 'waiting')
                            <x-forms.link-secondary link="javascript:;" class="mr-3" icon="times"
                                                    id="decline-estimate">@lang('app.decline')
                            </x-forms.link-secondary>

                            <x-forms.link-primary link="javascript:;" icon="check" data-toggle="modal"
                                                  data-target="#signature-modal">@lang('app.accept')
                            </x-forms.link-primary>
                        @endif

                    </div>
                </div>
                <!-- CARD FOOTER END -->
            </div>
            <!-- INVOICE CARD END -->

            <div id="signature-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog d-flex justify-content-center align-items-center modal-xl">
                    <div class="modal-content">
                        @include('estimates.ajax.accept-estimate')
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- also the modal itself -->
<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog d-flex justify-content-center align-items-center modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHeading">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                {{__('app.loading')}}
            </div>
            <div class="modal-footer">
                <button type="button" class="mr-3 rounded btn-cancel" data-dismiss="modal">Close</button>
                <button type="button" class="rounded btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Global Required Javascript -->
<script src="{{ asset('js/main.js') }}"></script>

<script>
    document.loading = '@lang('app.loading')';
    const MODAL_LG = '#myModal';
    const MODAL_HEADING = '#modelHeading';
    const dropifyMessages = {
        default: '@lang("app.dragDrop")',
        replace: '@lang("app.dragDropReplace")',
        remove: '@lang("app.remove")',
        error: '@lang("app.largeFile")'
    };

    $(window).on('load', function () {
        // Animate loader off screen
        init();
        $(".preloader-container").fadeOut("slow", function () {
            $(this).removeClass("d-flex");
        });
    });

    $(body).on('click', '#download-invoice', function () {
        window.location.href = "{{ route('invoices.download', [$estimate->id]) }}";
    })
</script>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script>
    var canvas = document.getElementById('signature-pad');

    var signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
    });

    document.getElementById('clear-signature').addEventListener('click', function (e) {
        e.preventDefault();
        signaturePad.clear();
    });

    document.getElementById('undo-signature').addEventListener('click', function (e) {
        e.preventDefault();
        var data = signaturePad.toData();
        if (data) {
            data.pop(); // remove the last dot or line
            signaturePad.fromData(data);
        }
    });

    $('#decline-estimate').click(function () {
        $.easyAjax({
            type: 'POST',
            url: "{{ route('front.estimate.decline', $estimate->id) }}",
            blockUI: true,
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });

    $('#toggle-pad-uploader').click(function () {
        var text = $('.signature').hasClass('d-none') ? '{{ __("modules.estimates.uploadSignature") }}' : '{{ __("app.sign") }}';

        $(this).html(text);

        $('.signature').toggleClass('d-none');
        $('.upload-image').toggleClass('d-none');
    });

    $('#save-signature').click(function () {
        var first_name = $('#first_name').val();
        var last_name = $('#last_name').val();
        var email = $('#email').val();
        var signature = signaturePad.toDataURL('image/png');

        var image = $('#image').val();

        // this parameter is used for type of signature used and will be used on validation and upload signature image
        var signature_type = !$('.signature').hasClass('d-none') ? 'signature' : 'upload';

        if (signaturePad.isEmpty() && !$('.signature').hasClass('d-none')) {
            Swal.fire({
                icon: 'error',
                text: '{{ __('messages.signatureRequired') }}',

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
            url: "{{ route('front.estimate.accept', $estimate->id) }}",
            container: '#acceptEstimate',
            type: "POST",
            blockUI: true,
            file: true,
            disableButton: true,
            buttonSelector: '#save-signature',
            data: {
                first_name: first_name,
                last_name: last_name,
                email: email,
                signature: signature,
                image: image,
                signature_type: signature_type,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });

    $('body').on('click', '.img-lightbox', function () {
        var imageUrl = $(this).data('image-url');
        const url = "{{ route('front.public.show_image').'?image_url=' }}" + imageUrl;
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>

</body>

</html>
