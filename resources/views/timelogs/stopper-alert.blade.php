<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.timeLogs.stopTimer')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="stopperTimer">
        <div class="row">
            <input type="hidden" value="{{$timeLog->id}}" name="timeId">
            <div class="bootstrap-timepicker timepicker col-sm-6">
                <x-forms.label class="form-group my-3" fieldId="start_time" :fieldLabel="__('modules.timeLogs.startTime')"></x-forms.label>
                <input type="text" class="form-control height-35 f-14" id="start_time"
                name="start_time" placeholder="" readonly
                value="{{ (!is_null($timeLog->start_time)) ? $timeLog->start_time->timezone(company()->timezone)->format(company()->time_format) : '' }}">
            </div>
            <div class="bootstrap-timepicker timepicker col-sm-6">
                <x-forms.label class="form-group my-3" fieldId="end_time" :fieldLabel="__('modules.timeLogs.endTime')"></x-forms.label>
                <input type="text" class="form-control height-35 f-14" id="end_time"
                name="end_time" placeholder="" readonly
                value="{{ now()->timezone(company()->timezone)->format(company()->time_format) }}">
            </div>
            <div class="bootstrap-timepicker timepicker col-sm-6">
                <x-forms.label class="form-group my-3" fieldId="total_time" :fieldLabel="__('modules.timeLogs.totalTime')"></x-forms.label>
                <input type="text" class="form-control height-35 f-14" id="total_time"
                name="total_time" placeholder="" readonly
                value="{{ $timeLogg }}">
            </div>
            <div class="col-sm-12">
                <x-forms.textarea fieldId="memo" :fieldLabel="__('modules.timeLogs.memo')"
                    fieldName="memo" fieldRequired="true" fieldPlaceholder="">
                </x-forms.textarea>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-stopper_time" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('#save-stopper_time').click(function() {
        var url = "{{ route('timelogs.stop_timer') }}";
        $.easyAjax({
            url: url,
            container: '#stopperTimer',
            type: "POST",
            data: $('#stopperTimer').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });
</script>
