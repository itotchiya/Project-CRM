<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.estimateRequest.sendEstimateRequest')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="confirmPaid">
        <div class="row">
            <div class="col-lg-7 col-lg-4 my-3" id="client_list_ids">
                <x-forms.select fieldName="client_id" fieldId="client_id" fieldRequired="true"
                    :fieldLabel="__('app.client')" data-live-search="true" data-size="8">
                    <option value="">--</option>
                    @foreach ($clients as $clientOpt)
                        <option data-content="<x-client-search-option :user='$clientOpt' />"
                        value="{{ $clientOpt->id }}">{{ $clientOpt->name }} </option>
                    @endforeach
                </x-forms.select>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-confirm_paid" icon="paper-plane">@lang('app.send')</x-forms.button-primary>
</div>

<script>
    $(document).ready(function() {
        $('#client_id').selectpicker();
    });
    $('#save-confirm_paid').click(function() {
        var url = "{{ route('estimate-request.send_estimate_mail') }}";
        let token = "{{ csrf_token() }}";

        $.easyAjax({
            url: url,
            type: "POST",
            disableButton: true,
            blockUI: true,
            data: {
                '_token': token,
                client_id: $('#client_id').val(),
            },
            success: function(response) {
                if (response.status == "success") {
                    $(MODAL_LG).modal('hide');
                    showTable();
                }
            }
        })
    });
</script>
