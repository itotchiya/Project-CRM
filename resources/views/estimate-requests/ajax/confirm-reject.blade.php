<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.estimateRequest.confirmReject')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="confirmPaid">
        <div class="row">
            <div class="col-md-12">
                <x-forms.text fieldId="reason" :fieldLabel="__('app.reason')"
                    fieldName="reason" fieldRequired="true" :fieldPlaceholder="__('placeholders.estimateRequest.reason')">
                </x-forms.text>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-confirm_paid" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('#save-confirm_paid').click(function() {
        var url = "{{ route('estimate-request.change_status') }}";
        let token = "{{ csrf_token() }}";
        let status = 'rejected';

        $.easyAjax({
            url: url,
            type: "POST",
            disableButton: true,
            blockUI: true,
            data: {
                '_token': token,
                id: {{ $estimateRequest->id }},
                status: status,
                reason: $('#reason').val(),
            },
            success: function(response) {
                if (response.status == "success") {
                    $(MODAL_LG).modal('hide');
                    location.reload();
                    showTable();
                }
            }
        })
    });
</script>
