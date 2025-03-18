<div class="col-xl-12 col-lg-12 col-md-12 w-100 p-20">

    @if($errors->any())
        <x-alert type="danger">
            {{$errors->first()}}
        </x-alert>
    @endif

    @if (session('connect_success'))
        <x-alert type="success">
            {{ session('connect_success') }}
        </x-alert>
    @endif

    <div class="row">
        <div class="col-lg-12 mb-3">
            <x-forms.checkbox :fieldLabel="__('app.status')" fieldName="status"
                fieldId="quickbook_status" fieldValue="1"
                :checked="$quickbookSetting->status" />
        </div>
    </div>

    <div class="row @if ($quickbookSetting->status == 0) d-none @endif" id="paypal_details">
        <div class="col-lg-4">
            <x-forms.select fieldId="paypal_mode" :fieldLabel="__('app.selectEnvironment')" fieldName="environment">
                <option value="Development" @if ($quickbookSetting->environment == 'Development') selected @endif>@lang('app.sandbox')</option>
                <option value="Production" @if ($quickbookSetting->environment == 'Production') selected @endif>@lang('app.live')</option>
            </x-forms.select>
        </div>

        <div
            @class([
                'col-lg-8',
                'd-none' => ($quickbookSetting->environment == 'Production')
            ]) id="sandbox_paypal_details">
            <div  class="row">
                <div class="col-lg-6">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" fieldLabel="Client ID"
                        fieldName="sandbox_client_id" fieldId="sandbox_client_id"
                        :fieldValue="$quickbookSetting->sandbox_client_id" fieldRequired="true"
                        fieldPlaceholder="">
                    </x-forms.text>
                </div>
                <div class="col-lg-6">
                    <x-forms.label class="mt-3" fieldId="sandbox_client_secret" fieldLabel="Client Secret"
                        fieldRequired="true">
                    </x-forms.label>
                    <x-forms.input-group>
                        <input type="password" name="sandbox_client_secret" id="sandbox_client_secret"
                            class="form-control height-35 f-14" value="{{ $quickbookSetting->sandbox_client_secret }}">
                        <x-slot name="preappend">
                            <button type="button" data-toggle="tooltip" data-original-title="@lang('app.viewPassword')"
                                class="btn btn-outline-secondary border-grey height-35 toggle-password"><i
                                    class="fa fa-eye"></i></button>
                        </x-slot>
                    </x-forms.input-group>
                </div>
            </div>
        </div>

        <div
            @class([
                'col-lg-8',
                'd-none' => ($quickbookSetting->environment == 'Development')
            ]) id="live_paypal_details">
            <div class="row">
                <div class="col-lg-6">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" fieldLabel="Client ID"
                        fieldName="client_id" fieldId="client_id"
                        :fieldValue="$quickbookSetting->client_id" fieldRequired="true" >
                    </x-forms.text>
                </div>
                <div class="col-lg-6">
                    <x-forms.label class="mt-3" fieldId="client_secret" fieldLabel="Client Secret"
                        fieldRequired="true">
                    </x-forms.label>
                    <x-forms.input-group>
                        <input type="password" name="client_secret" id="client_secret"
                            class="form-control height-35 f-14" value="{{ $quickbookSetting->client_secret }}">
                        <x-slot name="preappend">
                            <button type="button" data-toggle="tooltip" data-original-title="@lang('app.viewPassword')"
                                class="btn btn-outline-secondary border-grey height-35 toggle-password"><i
                                    class="fa fa-eye"></i></button>
                        </x-slot>
                    </x-forms.input-group>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <x-forms.label fieldId="" for="mail_from_name" :fieldLabel="__('modules.invoiceSettings.quickbooksRedirectUrl')">
            </x-forms.label>
            <p class="text-bold"><span id="webhook-link-text">{{ route('quickbooks.callback', company()->hash) }}</span>
                <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded p-1 py-2 ml-1"
                    data-clipboard-target="#webhook-link-text">
                    <i class="fa fa-copy mx-1"></i>@lang('app.copy')</a>
            </p>
            <p class="text-primary">(@lang('modules.invoiceSettings.addQuickbooksRedirectUrl'))</p>
        </div>

        @if($quickbookSetting->access_token == '' || $quickbookSetting->refresh_token == '')
            <div class="col-lg-12 mt-3">
                <x-alert type="danger">
                    @lang('modules.invoiceSettings.connectQuickBooks')
                </x-alert>
            </div>
        @endif


    </div>
</div>
<!-- Buttons Start -->
<div class="w-100 border-top-grey">
    <x-setting-form-actions>
        <div class="d-flex">
            <x-forms.button-primary class="mr-3" icon="check" id="save-quickbooks">@lang('app.save')
            </x-forms.button-primary>

            @if($quickbookSetting->status)
                @if($quickbookSetting->access_token == '' || $quickbookSetting->refresh_token == '')
                    <a href="{{ route('quickbooks.index') }}"><img src="{{ asset('img/C2QB_auth.png') }}" class="height-35"></a>
                @else
                    <x-forms.link-secondary class="mr-3" icon="key" :link="route('quickbooks.index')">@lang('modules.invoiceSettings.reauthorizeQuickBooks')
                    </x-forms.link-secondary>
                @endif
            @endif
        </div>
    </x-setting-form-actions>
</div>
<!-- Buttons End -->

<script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
<script>

var clipboard = new ClipboardJS('.btn-copy');

    clipboard.on('success', function(e) {
        Swal.fire({
            icon: 'success',
            text: '@lang("app.webhookUrlCopied")',
            toast: true,
            position: 'top-end',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            customClass: {
                confirmButton: 'btn btn-primary',
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
        })
    });

    $("body").on("change", "#quickbook_status", function (event) {
        $('#paypal_details').toggleClass('d-none');
    });

    $("body").on("change", "#paypal_mode", function () {
        $('#sandbox_paypal_details').toggleClass('d-none');
        $('#live_paypal_details').toggleClass('d-none');
    });


    // save invoice setting
    $('#save-quickbooks').click(function () {
        $.easyAjax({
            url: "{{ route('quickbooks-settings.update', $quickbookSetting->id) }}",
            container: '#editSettings',
            type: "POST",
            redirect: true,
            file: true,
            data: $('#editSettings').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-quickbooks",
        })
    });

</script>
