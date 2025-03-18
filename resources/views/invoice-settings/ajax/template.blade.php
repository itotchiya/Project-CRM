<link rel="stylesheet" href="{{ asset('vendor/css/image-picker.min.css') }}">

<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    @method('POST')
    <div class="row">
        <div class="col-lg-12 mt-4">
            <div class="form-group">
                <x-forms.label fieldId="template" :fieldLabel="__('modules.invoiceSettings.template')" fieldRequired="true">
                </x-forms.label>
                <select name="template" class="image-picker show-labels show-html">
                    <option data-img-src="{{ asset('img/invoice-template/1.png') }}"
                        @if ($invoiceSetting->template == 'invoice-1') selected @endif value="invoice-1">@lang('modules.invoiceSettings.template') 1
                    </option>
                    <option data-img-src="{{ asset('img/invoice-template/2.png') }}"
                        @if ($invoiceSetting->template == 'invoice-2') selected @endif value="invoice-2">@lang('modules.invoiceSettings.template') 2
                    </option>
                    <option data-img-src="{{ asset('img/invoice-template/3.png') }}"
                        @if ($invoiceSetting->template == 'invoice-3') selected @endif value="invoice-3">@lang('modules.invoiceSettings.template') 3
                    </option>
                    <option data-img-src="{{ asset('img/invoice-template/4.png') }}"
                        @if ($invoiceSetting->template == 'invoice-4') selected @endif value="invoice-4">@lang('modules.invoiceSettings.template') 4
                    </option>
                    <option data-img-src="{{ asset('img/invoice-template/5.png') }}"
                        @if ($invoiceSetting->template == 'invoice-5') selected @endif value="invoice-5">@lang('modules.invoiceSettings.template') 5
                    </option>
                </select>
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
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('invoice_settings.update_template', $invoiceSetting->id) }}",
            container: '#editSettings',
            type: "POST",
            redirect: true,
            file: true,
            data: $('#editSettings').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-form",
        });
    });
</script>
