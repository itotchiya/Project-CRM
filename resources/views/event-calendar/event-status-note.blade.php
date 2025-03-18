<div class="modal-header">
    @if ($status == 'completed')
        <h5 class="modal-title" id="modelHeading">@lang('modules.events.eventCompleteNote')</h5>
    @endif
    @if ($status == 'cancelled')
        <h5 class="modal-title" id="modelHeading">@lang('modules.events.eventCancelNote')</h5>
    @endif
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="eventStatusNote">
        <div class="row">
            <input type="hidden" value="{{ $status }}" name="status">
            <div class="col-sm-12">
                <x-forms.textarea fieldId="note" :fieldLabel="__('modules.lead.note')"
                    fieldName="note" fieldPlaceholder="">
                </x-forms.textarea>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-event-status" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

$('#save-event-status').click(function() {
        var url = "{{ route('events.update_status', $event->id) }}";
        $.easyAjax({
            url: url,
            container: '#eventStatusNote',
            type: "POST",
            disableButton: true,
            blockUI: true,
            data: $('#eventStatusNote').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });

</script>

