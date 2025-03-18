@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                <div class="row">
                    <div class="col-lg-3">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.invoiceSettings.contractPrefix')" :fieldPlaceholder="__('placeholders.invoices.contractPrefix')" fieldName="contract_prefix"
                            fieldRequired="true" fieldId="contract_prefix" :fieldValue="$contractSetting->contract_prefix" />
                    </div>

                    <div class="col-lg-3">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.invoiceSettings.contractNumberSeparator')" :fieldPlaceholder="__('placeholders.invoices.contractNumberSeparator')"
                            fieldName="contract_number_separator" fieldId="contract_number_separator" :fieldValue="$contractSetting->contract_number_separator" />
                    </div>

                    <div class="col-lg-3">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.invoiceSettings.contractDigit')" fieldName="contract_digit"
                            fieldId="contract_digit" :fieldValue="$contractSetting->contract_digit" minValue="2" />
                    </div>

                    <div class="col-lg-3">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.invoiceSettings.contractLookLike')" fieldName="contract_look_like"
                            fieldId="contract_look_like" fieldValue="" fieldReadOnly="true" />
                    </div>

                </div>
            </div>

            <x-slot name="action">
                <!-- Buttons Start -->
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
                <!-- Buttons End -->
            </x-slot>

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>

    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('contract-settings.update', $contractSetting->id) }}",
            container: '#editSettings',
            type: "POST",
            redirect: true,
            file: true,
            data: $('#editSettings').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-form",
        })
    });

        $('#contract_prefix, #contract_number_separator, #contract_digit').on('keyup', function() {
        genrateInvoiceNumber();
    });

    genrateInvoiceNumber();

    function genrateInvoiceNumber() {
        var contractPrefix = $('#contract_prefix').val();
        var contractNumberSeparator = $('#contract_number_separator').val();
        var contractDigit = $('#contract_digit').val();
        var contractZero = '';
        for ($i = 0; $i < contractDigit - 1; $i++) {
            contractZero = contractZero + '0';
        }
        contractZero = contractZero + '1';
        var contract_no = contractPrefix + contractNumberSeparator + contractZero;
        $('#contract_look_like').val(contract_no);
    }
    </script>
@endpush
