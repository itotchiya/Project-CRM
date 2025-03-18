<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">
        @lang('modules.invoices.payOffline')
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="offline-payment" method="POST" class="ajax-form">
            <input type="hidden" name="orderID" value="{{$orderID}}">

            <div class="d-flex justify-content-between">
                <div class="pt-2">
                    <i class="fa fa-exclamation-triangle"></i> @lang('messages.requestSentOffline')
                </div>
            </div>

            <div class="form-body">
                <div class="row" id="addressDetail">
                    <div class="col-lg-12 col-md-12">
                        <x-forms.select class="select-picker" fieldId="offlineMethod"
                                        :fieldLabel="__('modules.invoices.paymentMethod')"
                                        fieldName="offlineMethod">
                            @foreach($methods as $method)
                                @php
                                    $description = "<p class='my-0 f-11 text-dark-grey'>$method->description</p>"
                                @endphp
                                <option value="{{ $method->id }}"
                                        data-content="{!! '<strong>'.$method->name."</strong> ".$description !!}"
                                >
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-12 col-md-12">
                        <x-forms.file allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg"
                                      class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.receipt')"
                                      fieldName="bill" fieldId="bill"
                                      :popover="__('messages.fileFormat.multipleImageFile')"/>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-offline-payment" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(".select-picker").selectpicker();

    $("#bill").dropify({
        messages: dropifyMessages
    });

    $('#save-offline-payment').click(function () {
        $.easyAjax({
            url: "{{ route('invoices.store_offline_payment') }}",
            container: '#offline-payment',
            type: "POST",
            redirect: true,
            file: true,
            disableButton: true,
            buttonSelector: '#save-offline-payment',
            data: $('#offline-payment').serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = response.redirectUrl;
                }
            }
        })
    })
</script>
