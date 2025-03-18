<x-form id="updateMethods" method="PUT" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title">@lang('app.updateofflinePaymentMethod')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    </div>
    <div class="modal-body">
        <div class="portlet-body">

                <div class="form-body">

                    <div class="form-group">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.offlinePayment.method')"
                        :fieldPlaceholder="__('placeholders.offlinePayment.method')" fieldName="name" fieldId="name" :fieldValue="$method->name" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="form-group">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                        :fieldLabel="__('modules.offlinePayment.description')" fieldName="description"
                        fieldId="description" :fieldPlaceholder="__('placeholders.offlinePayment.description')" :fieldValue="$method->description" fieldRequired="true">
                        </x-forms.textarea>
                    </div>

{{--                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2"--}}
{{--                                  :fieldLabel="__('app.qrCode')"--}}
{{--                                  :fieldValue="($method->image ? $method->masked_image_url : $method->image_url)" fieldName="image"--}}
{{--                                  fieldId="image" >--}}
{{--                    </x-forms.file>--}}

                    <div class="form-group">
                        <x-forms.select fieldId="offline_payment_status" :fieldLabel="__('app.status')" fieldName="status" search="true">
                            <option value="yes" @if($method->status == 'yes') selected @endif>@lang('modules.offlinePayment.active')</option>
                            <option value="no" @if($method->status == 'no') selected @endif>@lang('modules.offlinePayment.inActive')</option>
                        </x-forms.select>
                    </div>

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

    // initialize select picker
    $('#offline_payment_status').selectpicker();

    // update offline methods
    $('#save-method').click(function () {
        var url =  "{{route('offline-payment-setting.update', $method->id)}}";
        $.easyAjax({
            url: url,
            container: '#updateMethods',
            type: "POST",
            disableButton: true,
            buttonSelector: "#save-method",
            blockUI: true,
            file:true,
            data: $('#updateMethods').serialize(),
            success: function (response) {
                window.location.reload();
            }
        })
    });
</script>

