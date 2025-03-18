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

<div class="card border-0 invoice">
    <!-- CARD BODY START -->
    <div class="card-body">
        <div class="invoice-table-wrapper">
            <table width="100%" class="">
                <tr class="inv-logo-heading">
                    <td><img src="{{ invoice_setting()->logo_url }}" alt="{{ company()->company_name }}"
                             class="logo"/></td>
                    <td align="right" class="font-weight-bold f-21 text-dark text-uppercase mt-4 mt-lg-0 mt-md-0">
                        @lang('app.menu.contract')</td>
                </tr>
                <tr class="inv-num">
                    <td class="f-14 text-dark">
                        <p class="mt-3 mb-0">
                            {{ company()->company_name }}<br>
                            {!! nl2br(default_address()->address) !!}<br>
                            {{ company()->company_phone }}
                        </p><br>
                    </td>
                    <td align="right">
                        <table class="inv-num-date text-dark f-13 mt-3">
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">
                                    @lang('modules.contracts.contractNumber')</td>
                                <td class="border-left-0">{{ $contract->contract_number }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">
                                    @lang('modules.projects.startDate')</td>
                                <td class="border-left-0">{{ $contract->start_date->translatedFormat(company()->date_format) }}
                                </td>
                            </tr>
                            @if ($contract->end_date != null)
                                <tr>
                                    <td class="bg-light-grey border-right-0 f-w-500">@lang('modules.contracts.endDate')
                                    </td>
                                    <td class="border-left-0">{{ $contract->end_date->translatedFormat(company()->date_format) }}
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
                    @if ($contract->client->clientDetails->company_logo)
                        <td align="right">
                            <img src="{{ $contract->client->clientDetails->image_url }}"
                                 alt="{{ $contract->client->clientDetails->company_name }}" class="logo"/>
                        </td>
                    @endif
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
            <div class="ql-editor p-0 pb-3">{!! $contract->contract_detail !!}</div>

            @if ($contract->amount != 0)
                <div class="text-right pt-3 border-top">
                    <h4>@lang('modules.contracts.contractValue'):
                        {{ currency_format($contract->amount, $contract->currency->id) }}</h4>
                </div>
            @endif
        </div>
        <hr class="mt-1 mb-1">
        <div class="mt-3">
            @if ($contract->signature)
                <div class="d-flex flex-column float-right">
                    <h6>@lang('modules.estimates.clientsignature')</h6>
                    <img src="{{$contract->signature->signature}}" style="width: 200px;">
                    <p>{{ $contract->signature->full_name }}<br>
                        @lang('app.place'): {{ $contract->signature->place }}<br>
                        @lang('app.date'): {{ $contract->signature->date->timezone($company->timezone) }}</p>
                </div>
            @endif

            @if ($contract->company_sign)
                <div class="d-flex flex-column">
                    <h6>@lang('modules.estimates.companysignature')</h6>
                    <img src="{{$contract->company_signature}}" style="width: 200px;">
                    <p>@lang('app.date'): {{ $contract->sign_date->timezone($company->timezone) }}</p>
                    @if($contract->signer)
                        <p style="margin-top: -16px;">@lang('app.signBy') : {{ $contract->signer ? $contract->signer->name : '--' }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div id="signature-mod" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog d-flex justify-content-center align-items-center modal-xl">
                <div class="modal-content">
                    @include('estimates.ajax.accept-estimate')
                </div>
            </div>
        </div>
    </div>
    <!-- CARD BODY END -->
    <!-- CARD FOOTER START -->
    <div class="card-footer bg-white border-0 d-flex justify-content-start py-0 py-lg-4 py-md-4 mb-4 mb-lg-3 mb-md-3 ">

        <div class="d-flex">
            <div class="inv-action mr-3 mr-lg-3 mr-md-3 dropup">
                <button class="dropdown-toggle btn-secondary" type="button" id="dropdownMenuButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@lang('app.action')
                    <span><i class="fa fa-chevron-down f-15 text-dark-grey"></i></span>
                </button>
                <!-- DROPDOWN - INFORMATION -->
                <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="dropdownMenuButton" tabindex="0">
                    @if (!$contract->signature)
                        <li>
                            <a class="dropdown-item"
                               href="{{ url()->temporarySignedRoute('front.contract.show', now()->addDays(\App\Models\GlobalSetting::SIGNED_ROUTE_EXPIRY), $contract->hash) }}"
                               target="_blank"><i class="fa fa-link mr-2"></i>@lang('modules.proposal.publicLink')</a>
                        </li>
                    @endif
                    @if ($addContractPermission == 'all' || $addContractPermission == 'added')
                        <li>
                            <a class="dropdown-item openRightModal"
                               href="{{ route('contracts.create') . '?id=' . $contract->id }}">
                                <i class="fa fa-copy mr-2"></i>
                                @lang('app.copyContract')
                            </a>
                        </li>
                    @endif
                    @if (!in_array('client',user_roles()) && !$contract->company_sign && user()->company_id == $contract->company_id)
                        <li>
                            <a class="dropdown-item f-14 text-dark" href="javascript:;"
                               id="company-signature">
                                <i class="fa fa-check f-w-500  mr-2 f-12"></i>
                                @lang('modules.estimates.companysignature')
                            </a>
                        </li>
                    @endif
                    @if (
                    $editContractPermission == 'all'
                    || ($editContractPermission == 'added' && user()->id == $contract->added_by)
                    || ($editContractPermission == 'owned' && user()->id == $contract->client_id)
                    || ($editContractPermission == 'both' && (user()->id == $contract->client_id || user()->id == $contract->added_by))
                    )
                        <li>
                            <a class="dropdown-item openRightModal"
                               href="{{ route('contracts.edit', [$contract->id]) }}">
                                <i class="fa fa-edit mr-2"></i>@lang('app.edit')
                            </a>
                        </li>
                    @endif
                    @if (!$contract->signature && user()->id == $contract->client->id)
                        <li>
                            <a class="dropdown-item f-14 text-dark" href="javascript:;" data-toggle="modal"
                               data-target="#signature-mod">
                                <i class="fa fa-check f-w-500  mr-2 f-12"></i>
                                @lang('app.sign')
                            </a>
                        </li>
                    @endif
                    <li>
                        <a class="dropdown-item f-14 text-dark"
                           href="{{ route('contracts.download', $contract->id) }}">
                            <i class="fa fa-download f-w-500 mr-2 f-11"></i> @lang('app.download')
                        </a>
                    </li>
                    @if (
                    $deleteContractPermission == 'all'
                    || ($deleteContractPermission == 'added' && user()->id == $contract->added_by)
                    || ($deleteContractPermission == 'owned' && user()->id == $contract->client_id)
                    || ($deleteContractPermission == 'both' && (user()->id == $contract->client_id || user()->id == $contract->added_by))
                    )
                        <li>
                            <a class="dropdown-item delete-table-row" href="javascript:;"
                               data-contract-id="{{ $contract->id }}">
                                <i class="fa fa-trash mr-2"></i>@lang('app.delete')
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <x-forms.button-cancel :link="route('contracts.index')" class="border-0">@lang('app.cancel')
            </x-forms.button-cancel>

        </div>


    </div>
    <!-- CARD FOOTER END -->
</div>
<!-- INVOICE CARD END -->

{{-- Custom fields data --}}
@if (isset($fields) && count($fields) > 0)
    <div class="row mt-4">
        <!-- TASK STATUS START -->
        <div class="col-md-12">
            <x-cards.data>
                <h5 class="mb-3"> @lang('modules.projects.otherInfo')</h5>
                <x-forms.custom-field-show :fields="$fields" :model="$contract"></x-forms.custom-field-show>
            </x-cards.data>
        </div>
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script>

    $('#company-signature').click(function () {
        const url = "{{ route('contracts.company_sig', $contract->id) }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
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
                text: "{{ __('messages.signatureRequired') }}",

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
            url: "{{ route('contracts.sign', $contract->id) }}",
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
        })
    });

    $('body').on('click', '.delete-table-row', function () {
        var id = $(this).data('contract-id');
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
                var url = "{{ route('contracts.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function (response) {
                        if (response.status == "success") {
                            window.location.href = "{{ route('contracts.index') }}"
                        }
                    }
                });
            }
        });
    });
    var canvas = document.getElementById('sign-pad');

    var signPad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
    });

    document.getElementById('clear-sign').addEventListener('click', function (e) {
        e.preventDefault();
        signPad.clear();
    });

    document.getElementById('undo-sign').addEventListener('click', function (e) {
        e.preventDefault();
        var data = signPad.toData();
        if (data) {
            data.pop(); // remove the last dot or line
            signPad.fromData(data);
        }
    });

    $('#toggle-pad-upload').click(function () {
        var text = $('.signature').hasClass('d-none') ? '{{ __("modules.estimates.uploadSignature") }}' : '{{ __("app.sign") }}';

        $(this).html(text);

        $('.signature').toggleClass('d-none');
        $('.upload-img').toggleClass('d-none');
    });

    $('#save-sign').click(function () {
        var signature = signPad.toDataURL('image/png');
        var image = $('#sign_image').val();

        // this parameter is used for type of signature used and will be used on validation and upload signature image
        var signature_type = !$('.signature').hasClass('d-none') ? 'signature' : 'upload';

        if (signPad.isEmpty() && !$('.signature').hasClass('d-none')) {
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
    });

</script>
