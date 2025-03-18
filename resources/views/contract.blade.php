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
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $company->favicon_url }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ $company->favicon_url }}">
    <meta name="theme-color" content="#ffffff">

    @include('sections.theme_css', ['company' => $company])

    @isset($activeSettingMenu)
        <style>
            .preloader-container {
                margin-left: 510px;
                width: calc(100% - 510px)
            }

        </style>
    @endisset

    <style>
        .logo {
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

    </style>

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

    </style>


    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/modernizr.min.js') }}"></script>

    <script>
        var checkMiniSidebar = localStorage.getItem("mini-sidebar");

    </script>

</head>

<body id="body" class="h-100 bg-additional-grey {{ isRtl('rtl') }}">

<div class="container content-wrapper">

    <div class="border-0 card invoice">
        <!-- CARD BODY START -->
        <div class="card-body">
            <div class="invoice-table-wrapper">
                <table width="100%" class="">
                    <tr class="inv-logo-heading">
                        <td><img src="{{ $invoiceSetting->logo_url }}" alt="{{ $company->company_name }}"
                                 class="logo"/></td>
                        <td align="right" class="mt-4 font-weight-bold f-21 text-dark text-uppercase mt-lg-0 mt-md-0">
                            @lang('app.menu.contract')</td>
                    </tr>
                    <tr class="inv-num">
                        <td class="f-14 text-dark">
                            <p class="mt-3 mb-0">
                                {{ $company->company_name }}<br>
                                {!! nl2br($company->defaultAddress->address) !!}<br>
                                {{ $company->company_phone }}
                            </p><br>
                        </td>
                        <td align="right">
                            <table class="mt-3 inv-num-date text-dark f-13">
                                <tr>
                                    <td class="bg-light-grey border-right-0 f-w-500">
                                        @lang('modules.contracts.contractNumber')</td>
                                    <td class="border-left-0"> {{ $contract->contract_number }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light-grey border-right-0 f-w-500">
                                        @lang('modules.projects.startDate')</td>
                                    <td class="border-left-0">{{ $contract->start_date->translatedFormat($company->date_format) }}
                                    </td>
                                </tr>
                                @if ($contract->end_date != null)
                                    <tr>
                                        <td class="bg-light-grey border-right-0 f-w-500">@lang('modules.contracts.endDate')
                                        </td>
                                        <td class="border-left-0">{{ $contract->end_date->translatedFormat($company->date_format) }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="bg-light-grey border-right-0 f-w-500">
                                        @lang('modules.contracts.contractType')</td>
                                    <td class="border-left-0">{{ $contract->contractType->name }}
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
                            <p class="mb-0 text-left"><span
                                    class="text-dark-grey ">@lang("app.client")</span><br>
                                {{ $contract->client->name_salutation }}<br>
                                {{ $contract->client->clientDetails->company_name }}<br>
                                {!! nl2br($contract->client->clientDetails->address) !!}</p>
                        </td>
                        <td align="right">
                            @if ($contract->client->clientDetails->company_logo)
                                <img src="{{ $contract->client->clientDetails->image_url }}"
                                     alt="{{ $contract->client->clientDetails->company_name }}"
                                     class="logo"/>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td height="30"></td>
                    </tr>
                </table>
            </div>

            <div class="d-flex flex-column">
                <h5>@lang('app.subject')</h5>
                <p class="f-15">{{ $contract->subject }}</p>
                <h5>@lang('modules.contracts.notes')</h5>
                <p class="f-15">{{ $contract->contract_note }}</p>
                <h5>@lang('app.description')</h5>
                <div class="p-0 pb-3 ql-editor">{!! $contract->contract_detail !!}</div>

                @if ($contract->amount != 0)
                    <div class="pt-3 text-right border-top">
                        <h4>@lang('modules.contracts.contractValue'):
                            {{ currency_format($contract->amount, $contract->currency->id) }}</h4>
                    </div>
                @endif
            </div>

            <hr class="mt-1 mb-1">
            @if ($contract->signature)
                <div class="d-flex flex-column float-right margin-top: 20px;">
                    <h6>@lang('modules.estimates.clientsignature')</h6>
                    <img src="{{ $contract->signature->signature }}" style="width: 200px;">
                    <p>@lang('app.client_name'):- {{ $contract->signature->full_name }}<br>
                        @lang('app.place'):- {{ $contract->signature->place }}<br>
                        @lang('app.date'):- {{ $contract->signature->date->translatedFormat($company->date_format) }}
                    </p>
                </div>
            @endif

            @if ($contract->company_sign)
                <div class="d-flex flex-column">
                    <h6>@lang('modules.estimates.companysignature')</h6>
                    <img src="{{$contract->company_signature}}" style="width: 200px;">
                    <p>@lang('app.date'):- {{ $contract->sign_date->translatedFormat($company->date_format) }}</p>
                    @if($contract->signer)
                    <p style="margin-top: -16px;">@lang('app.signBy') : {{ $contract->signer ? $contract->signer->name : '--' }}</p>
                    @endif
                </div>
            @endif

            <div id="signature-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog d-flex justify-content-center align-items-center modal-xl">
                    <div class="modal-content">
                        @include('estimates.ajax.accept-estimate')
                    </div>
                </div>
            </div>

        </div>
        <!-- CARD BODY END -->

        <!-- CARD FOOTER START -->
        <div
            class="py-0 mb-4 bg-white border-0 card-footer d-flex justify-content-end py-lg-4 py-md-4 mb-lg-3 mb-md-3 ">

            <x-forms.button-cancel :link="route('contracts.index')" class="mb-2 mr-3 border-0">@lang('app.cancel')
            </x-forms.button-cancel>

            <x-forms.link-secondary :link="route('front.contract.download', $contract->hash)" class="mb-2 mr-3"
                                    icon="download">@lang('app.download')
            </x-forms.link-secondary>

            @if (!$contract->signature)
                <x-forms.link-primary class="mb-2" link="javascript:;" data-toggle="modal"
                                      data-target="#signature-modal" icon="check">@lang('app.sign')
                </x-forms.link-primary>
            @endif

        </div>
        <!-- CARD FOOTER END -->
    </div>
    <!-- INVOICE CARD END -->

    {{-- Custom fields data --}}
    @if (isset($fields) && count($fields) > 0)
        <div class="mt-4 row">
            <!-- TASK STATUS START -->
            <div class="col-md-12">
                <x-cards.data>
                    <x-forms.custom-field-show :fields="$fields" :model="$contract"></x-forms.custom-field-show>
                </x-cards.data>
            </div>
        </div>
    @endif
</div>

<!-- also the modal itself -->
<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog d-flex justify-content-center align-items-center modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHeading">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close"><span aria-hidden="true">×</span></button>
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

    $(window).on('load', function () {
        // Animate loader off screen
        init();
        $(".preloader-container").fadeOut("slow", function () {
            $(this).removeClass("d-flex");
        });
    });

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
            url: "{{ route('front.contract.sign', $contract->id) }}",
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

</script>

</body>

</html>
