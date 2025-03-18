<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.invoices.addPaymentDetails')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createPaymentDetails">
        <div class="row">
            <div class="col-sm-12">
                <x-forms.text fieldId="title" :fieldLabel="__('modules.invoices.title')"
                              fieldName="title" fieldRequired="true"
                              :fieldPlaceholder="__('placeholders.invoices.title')">
                </x-forms.text>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <x-forms.label fieldId="" class="text-capitalize" :fieldLabel="__('modules.invoices.paymentDetails')">
                </x-forms.label>
                <textarea class="form-control" name="payment_details" id="payment_details" rows="4"
                          placeholder="@lang('placeholders.invoices.BankDetails')"></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 "
                                  :fieldLabel="__('app.qrCode')"
                                  fieldName="image"
                                  fieldId="image">
                    </x-forms.file>
                </div>
            </div>
        </div>
    </x-form>
</div>

<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-payment-details" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $("#image").dropify({
        messages: dropifyMessages
    });
    $('#save-payment-details').click(function () {
        var url = "{{ route('invoices-payment-details.store') }}";
        $.easyAjax({
            url: url,
            container: '#createPaymentDetails',
            type: "POST",
            blockUI: true,
            file: true,
            data: $('#createPaymentDetails').serialize(),
            success: function (response) {
                if (response.status == 'success') {
                    $(MODAL_LG).modal('hide');
                    window.location.reload();
                }
            }
        })
    });

</script>

