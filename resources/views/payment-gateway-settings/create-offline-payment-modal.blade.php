<x-form id="createMethods" method="POST" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title">@lang('app.addNewofflinePaymentMethod')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    </div>

    <div class="modal-body">
        <div class="portlet-body">

            <div class="form-body">
                <div class="form-group">
                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.offlinePayment.method')"
                                :fieldPlaceholder="__('placeholders.offlinePayment.method')" fieldName="name" fieldId="name" fieldRequired="true"></x-forms.text>
                </div>
                <div class="form-group">
                    <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                      :fieldLabel="__('modules.offlinePayment.description')" fieldName="description"
                                      fieldId="description" :fieldPlaceholder="__('placeholders.offlinePayment.description')" fieldRequired="true">
                    </x-forms.textarea>
                </div>
{{--                <div class="form-group">--}}
{{--                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 "--}}
{{--                                  :fieldLabel="__('app.qrCode')"--}}
{{--                                  fieldName="image"--}}
{{--                                  fieldId="image">--}}
{{--                    </x-forms.file>--}}
{{--                </div>--}}
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-method" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>
<script>

    // $("#image").dropify({
    //     messages: dropifyMessages
    // });
    //  save offline payments
    $('#save-method').click(function () {
        $.easyAjax({
            url: "{{route('offline-payment-setting.store')}}",
            container: '#createMethods',
            type: "POST",
            disableButton: true,
            buttonSelector: "#save-method",
            blockUI: true,
            file: true,
            data: $('#createMethods').serialize(),
            success: function () {
                window.location.reload();
            }
        })
    });
</script>

