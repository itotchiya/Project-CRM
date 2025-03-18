
<link rel="stylesheet" href="{{ asset('vendor/css/image-picker.min.css') }}">

<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    @method('PUT')

    <div class="row">

        <div class="col-lg-6">
            <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="__('modules.invoiceSettings.logo')"
                            fieldName="logo" fieldId="logo" :fieldValue="$invoiceSetting->masked_logo_url"
                            :popover="__('messages.invoiceLogoTooltip')"/>
        </div>
        <div class="col-lg-6">
            <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="__('modules.invoiceSettings.authorisedSignatorySignature')"
                            fieldName="authorised_signatory_signature" fieldId="authorised_signatory_signature" :fieldValue="$invoiceSetting->masked_authorised_signatory_signature_url"
                            :popover="__('messages.authorisedSignatorySignatureTooltip')"/>
        </div>


        <div class="col-lg-6">
            <x-forms.select fieldId="locale" :fieldLabel="__('modules.accountSettings.language')"
                            fieldName="locale" search="true">
                @foreach ($languageSettings as $language)
                    <option
                            data-content="<span class='flag-icon flag-icon-{{ ($language->flag_code == 'en') ? 'gb' : $language->flag_code }} flag-icon-squared'></span> {{ $language->language_name }}"
                            @if ($invoiceSetting->locale == $language->language_code) selected
                            @endif value="{{ $language->language_code }}">
                        {{ $language->language_name }}</option>
                @endforeach
            </x-forms.select>
        </div>

        <div class="col-lg-6">
            <x-forms.label class="mt-3" fieldId="due_after" fieldRequired="true"
                            :fieldLabel="__('modules.invoiceSettings.dueAfter')">
            </x-forms.label>
            <x-forms.input-group>
                <input type="number" value="{{ $invoiceSetting->due_after }}" name="due_after"
                        id="due_after"
                        class="form-control height-35 f-14" min="0">
                <x-slot name="append">
                    <span class="input-group-text height-35 bg-white border-grey">@lang('app.days')</span>
                </x-slot>
            </x-forms.input-group>
        </div>

        <div class="col-lg-6">
            <x-forms.label class="mt-3" fieldId="send_reminder" fieldRequired="false"
                            :fieldLabel="__('app.sendReminderBefore')">
            </x-forms.label>
            <x-forms.input-group>
                <input type="number" value="{{ $invoiceSetting->send_reminder }}" name="send_reminder"
                        id="send_reminder" class="form-control height-35 f-14" min="0">
                <x-slot name="append">
                    <span class="input-group-text height-35 bg-white border-grey">@lang('app.days')</span>
                </x-slot>
            </x-forms.input-group>
        </div>

        <div class="col-lg-6 mt-5">
            <x-forms.input-group>
                <x-forms.select class="border-right-0" fieldId="reminder" fieldLabel=""
                                fieldName="reminder">
                    <option
                        {{ ($invoiceSetting->reminder == 'after') ? 'selected' : '' }} value="after">@lang('app.sendReminderAfter')</option>
                    <option
                        {{ ($invoiceSetting->reminder == 'every') ? 'selected' : '' }} value="every">@lang('app.sendReminderEvery')</option>
                </x-forms.select>

                <input type="number" value="{{ $invoiceSetting->send_reminder_after }}"
                        name="send_reminder_after"
                        id="send_reminder_after" class="form-control height-35 f-14" min="0">
                <x-slot name="append">
                    <span class="input-group-text height-35 bg-white border-grey">@lang('app.days')</span>
                </x-slot>
            </x-forms.input-group>
        </div>


        <div class="col-lg-4 mt-5">
            <x-forms.checkbox :checked="$invoiceSetting->show_gst=='yes'" :fieldLabel="__('app.showGst')"
                                fieldName="show_gst" fieldId="show_gst"/>
        </div>

        <div class="col-lg-4 mt-5">
            <x-forms.checkbox :checked="$invoiceSetting->hsn_sac_code_show==1"
                                :fieldLabel="__('app.hsnSacCodeShow')" fieldName="hsn_sac_code_show"
                                fieldId="hsn_sac_code_show"/>
        </div>

        <div class="col-lg-4 mt-5">
            <x-forms.checkbox :checked="$invoiceSetting->tax_calculation_msg==1"
                                :fieldLabel="__('app.showTaxCalculationMessage')"
                                fieldName="show_tax_calculation_msg" fieldId="show_tax_calculation_msg"/>
        </div>

        <div class="col-lg-4 mt-2">
            <x-forms.checkbox :checked="$invoiceSetting->show_status==1"
                                :fieldLabel="__('app.showStatus')"
                                fieldName="show_status" fieldId="show_status" :popover="__('messages.invoiceStatusShowTooltip')"/>
        </div>

        <div class="col-lg-4 mt-2">
            <x-forms.checkbox :checked="$invoiceSetting->authorised_signatory==1"
                                :fieldLabel="__('app.showAuthorisedSignatory')"
                                fieldName="show_authorised_signatory" fieldId="show_authorised_signatory" :popover="__('messages.invoiceAuthorisedSignatoryShowTooltip')"/>
        </div>

        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-12 mb-2 mt-5">
                    <h5 class="heading-h4">@lang('modules.invoiceSettings.showFieldsInInvoice')</h5>
                </div>
                <div class="col-lg-4">
                    <x-forms.checkbox :checked="$invoiceSetting->show_client_name=='yes'"
                                        :fieldLabel="__('modules.client.clientName')"
                                        fieldName="show_client_name"
                                        fieldId="show_client_name"/>
                </div>
                <div class="col-lg-4">
                    <x-forms.checkbox :checked="$invoiceSetting->show_client_email=='yes'"
                                        :fieldLabel="__('modules.client.clientEmail')"
                                        fieldName="show_client_email"
                                        fieldId="show_client_email"/>
                </div>
                <div class="col-lg-4">
                    <x-forms.checkbox :checked="$invoiceSetting->show_client_phone=='yes'"
                                        :fieldLabel="__('modules.client.clientPhone')"
                                        fieldName="show_client_phone"
                                        fieldId="show_client_phone"/>
                </div>
                <div class="col-lg-4 mt-2">
                    <x-forms.checkbox :checked="$invoiceSetting->show_client_company_name=='yes'"
                                        :fieldLabel="__('modules.client.companyName')"
                                        fieldName="show_client_company_name"
                                        fieldId="show_client_company_name"/>
                </div>
                <div class="col-lg-4 mt-2">
                    <x-forms.checkbox :checked="$invoiceSetting->show_client_company_address=='yes'"
                                        :fieldLabel="__('app.client').' '.__('modules.client.address')"
                                        fieldName="show_client_company_address"
                                        fieldId="show_client_company_address"/>
                </div>

                <div class="col-lg-4 mt-2">
                    <x-forms.checkbox :checked="$invoiceSetting->show_project== 1"
                                        :fieldLabel="__('app.showProjectOnInvoice')" fieldName="show_project"
                                        fieldId="show_project"/>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mt-3">
            <div class="form-group my-3">
                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                    :fieldLabel="__('modules.invoiceSettings.invoiceTerms')"
                                    fieldName="invoice_terms"
                                    fieldId="invoice_terms"
                                    :fieldPlaceholder="__('placeholders.invoices.invoiceTerms')"
                                    :fieldValue="$invoiceSetting->invoice_terms">
                </x-forms.textarea>
            </div>
        </div>

        <div class="col-lg-12 mt-3">
            <div class="form-group my-3">
                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                    :fieldLabel="__('modules.invoiceSettings.otherInfo')"
                                    fieldName="other_info"
                                    fieldId="other_info"
                                    :fieldPlaceholder="__('placeholders.invoices.otherInfo')"
                                    :fieldValue="$invoiceSetting->other_info">
                </x-forms.textarea>
            </div>
        </div>

    </div>

</div>


<!-- Buttons Start -->
<div class="w-100 border-top-grey">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>
    </x-setting-form-actions>
</div>
<!-- Buttons End -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/image-picker/0.3.1/image-picker.min.js"></script>
<script>
    // Initializing image picker
    $('.image-picker').imagepicker();

    // save invoice setting
    $('#save-form').click(function () {
        $.easyAjax({
            url: "{{ route('invoice-settings.update', $invoiceSetting->id) }}",
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

    genrateInvoiceNumber();

    function genrateInvoiceNumber() {
        var invoicePrefix = $('#invoice_prefix').val();
        var invoiceNumberSeparator = $('#invoice_number_separator').val();
        var invoiceDigit = $('#invoice_digit').val();
        var invoiceZero = '';
        for ($i = 0; $i < invoiceDigit - 1; $i++) {
            invoiceZero = invoiceZero + '0';
        }
        invoiceZero = invoiceZero + '1';
        var invoice_no = invoicePrefix + invoiceNumberSeparator + invoiceZero;
        $('#invoice_look_like').val(invoice_no);

        var estimatePrefix = $('#estimate_prefix').val();
        var estimateNumberSeparator = $('#estimate_number_separator').val();
        var estimateDigit = $('#estimate_digit').val();
        var estimateZero = '';
        for ($i = 0; $i < estimateDigit - 1; $i++) {
            estimateZero = estimateZero + '0';
        }
        estimateZero = estimateZero + '1';
        var estimate_no = estimatePrefix + estimateNumberSeparator + estimateZero;
        $('#estimate_look_like').val(estimate_no);

        var creditNotePrefix = $('#credit_note_prefix').val();
        var creditNoteNumberSeparator = $('#credit_note_number_separator').val();
        var creditNoteDigit = $('#credit_note_digit').val();
        var creditNoteZero = '';
        for ($i = 0; $i < creditNoteDigit - 1; $i++) {
            creditNoteZero = creditNoteZero + '0';
        }
        creditNoteZero = creditNoteZero + '1';
        var creditNote_no = creditNotePrefix + creditNoteNumberSeparator + creditNoteZero;
        $('#credit_note_look_like').val(creditNote_no);

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

    $('#add-tax').click(function () {
        const url = "{{ route('taxes.create') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>
