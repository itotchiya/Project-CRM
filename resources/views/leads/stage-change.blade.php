<style>
    #stage {
        margin-top: -5px;
    }
</style>

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading" >@lang('modules.deal.changeDealStage')</h5>
</div>
<div class="modal-body">
    <div>
        <x-alert type="warning" icon="info-circle"> @lang('modules.deal.dealStageTooltip') </x-alert>
    </div>
    <x-form id="changeDealStage">
        <div class="row">
            <input type="hidden" value="{{$deal->id}}" name="dealId">
            <input type="hidden" value="{{$pipelineStageId}}" name="pipelineStageId">
            <input type="hidden" value="{{ __('modules.deal.remarks') }}({{$pipelineStageName}})" name="title">

            <div class="col-sm-6">
                <x-forms.datepicker fieldId="close_date" class="custom-date-picker" :fieldLabel="__('modules.deal.closeDate')"
                    fieldName="close_date" fieldRequired="true" :fieldPlaceholder="__('placeholders.date')"
                    :fieldValue="(($deal->close_date) ? $deal->close_date->format(company()->date_format) : '')"/>
            </div>

            <div class="col-sm-6">
                <x-forms.label class="form-group my-3" fieldId="" :fieldLabel="__('modules.leadContact.leadStage')"></x-forms.label>
                <input type="text" class="form-control height-35 f-14" id="stage"
                name="stage" placeholder="" readonly
                value="{{ $pipelineStageName }}">
            </div>
            <div class="col-sm-12">
                <x-forms.textarea fieldId="description" :fieldLabel="__('app.remark')"
                    fieldName="description" fieldPlaceholder="">
                </x-forms.textarea>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" id="close-modal-btn"  class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-deal-stage" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

    $('#close_date').each(function(ind, el) {
        datepicker(el, {
            position: 'bl',
            ...datepickerConfig
        });
    });

    $('#close-modal-btn').click(function() {
        showTable();
    });
    
    $('#save-deal-stage').click(function() {
        var url = "{{ route('deals.save_stage_change') }}";

        $.easyAjax({
            url: url,
            container: '#changeDealStage',
            type: "POST",
            data: $('#changeDealStage').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    $(MODAL_LG).modal('hide');
                }
                showTable();
            }
        })
    });

</script>
