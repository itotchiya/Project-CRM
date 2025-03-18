<style>
    #colorpicker .form-group {
        width: 87%;
    }
</style>

<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />

<x-form id="createStatus" method="POST" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('app.addNew') @lang('modules.deal.leadStage')</h5>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="portlet-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-12">
                            <x-forms.select fieldId="stagePipeline" :fieldLabel="__('modules.deal.pipeline')" fieldName="pipeline[]" fieldRequired="true" multiple="true">
                                @foreach ($pipelines as $pipeline)
                                    <option  value="{{ $pipeline->id }}">
                                        {{ $pipeline->name }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <x-forms.text fieldId="name" :fieldLabel="__('modules.deal.leadStage')"
                                fieldName="name" fieldRequired="true" :fieldPlaceholder="__('placeholders.status')">
                            </x-forms.text>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <div id="colorpicker" class="input-group">
                                <div class="form-group my-3 text-left">
                                    <x-forms.label fieldId="colorselector" :fieldLabel="__('modules.tasks.labelColor')"
                                        fieldRequired="true">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <input type="text" name="label_color" id="colorselector" value="#16813D"
                                            class="form-control height-35 f-15 light_text">
                                        <x-slot name="append">
                                            <span class="input-group-text colorpicker-input-addon height-35"><i></i></span>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
        <x-forms.button-primary id="save-status" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>
    $('#colorpicker').colorpicker({"color": "#16813D"});
    $('#stagePipeline').selectpicker();

    // save status
    $('#save-status').click(function() {
        $.easyAjax({
            url: "{{ route('lead-stage-setting.store') }}",
            container: '#createStatus',
            type: "POST",
            blockUI: true,
            disableButton: true,
            buttonSelector: "#save-status",
            data: $('#createStatus').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });
</script>
