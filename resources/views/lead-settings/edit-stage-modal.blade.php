<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />

<style>
    #colorpicker .form-group {
        width: 87%;
    }
</style>


<x-form id="editStatus" method="PUT" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('modules.deal.editDealStage')</h5>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="portlet-body">
                <div class="form-body">
                    <div class="row">

                        <input type="hidden" name="pipeline" value="{{$stage->lead_pipeline_id}}" >
                        <div class="col-sm-4 col-md-12 col-lg-6">
                            <x-forms.text fieldId="type" :fieldLabel="__('modules.deal.leadStage')"
                                fieldName="name" fieldRequired="true" :fieldPlaceholder="__('placeholders.status')" :fieldValue="$stage->name">
                            </x-forms.text>
                        </div>
                        <div class="col-sm-4 col-md-12 col-lg-6">
                            <div id="colorpicker" class="input-group">
                                <div class="form-group my-3 text-left">
                                    <x-forms.label fieldId="colorselector" :fieldLabel="__('modules.tasks.labelColor')"
                                        fieldRequired="true">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <input type="text" name="label_color" id="colorselector" value="{{ $stage->label_color }}"
                                            class="form-control height-35 f-15 light_text">
                                        <x-slot name="append">
                                            <span class="input-group-text colorpicker-input-addon height-35"><i></i></span>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                            </div>
                        </div>

                        {{-- <div class="col-sm-4 col-md-6">
                            <div class="form-group my-3 text-left">
                                <x-forms.label fieldId="priority" :fieldLabel="__('modules.tasks.position')" fieldRequired="false"> </x-forms.label>
                                <select class="form-control select-picker" id="priority" data-live-search="true"
                                    name="priority">
                                    @for($i=1; $i<= $maxPriority; $i++)
                                        <option @if($i == $stage->priority) selected @endif>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div> --}}

                        <div class="col-md-6">
                            <x-forms.select fieldId="priority" :fieldLabel="__('modules.tasks.position')" fieldName="priority"
                                search="true">
                                @php
                                    $firstPriority = min($stages->pluck('priority')->toArray());
                                    $priority = $stages->pluck('priority')->toArray();
                                @endphp

                                @foreach ($stages as $column)
                                    @if($column->id != $stage->id)
                                        @if ($column->priority == $firstPriority)
                                                <option value="{{$column->priority}}" priority-type="before">@lang('app.before') {{$column->name}}</option>
                                                <option value="{{$column->priority}}" @if(!is_null($lastStageColumn) && $lastStageColumn->id == $column->id) selected @endif>@lang('app.after') {{$column->name}}</option>

                                        @elseif (isset($afterStageColumn) && $afterStageColumn->priority == $column->priority)
                                                @if ($stage->priority == $firstPriority)
                                                    <option value="{{$column->priority - 1}}">@lang('app.before') {{$column->name}}</option>
                                                @endif
                                                    <option value="{{$column->priority}}">@lang('app.after') {{$column->name}}</option>
                                        @else
                                            <option value="{{$column->priority}}" @if(!is_null($lastStageColumn) && $lastStageColumn->id == $column->id) selected @endif>@lang('app.after') {{$column->name}}</option>
                                        @endif
                                    @endif
                                @endforeach
                            </x-forms.select>
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

    $('#colorpicker').colorpicker({"color": "{{ $stage->label_color }}"});

    $(".select-picker").selectpicker();

    // save status
    $('#save-status').click(function() {

        var priorityType = $("#priority").find(':selected').attr('priority-type');

        if(priorityType == "before"){
            var url = "{{route('lead-stage-setting.update', $stage->id)}}?before";
        }
        else{
            var url = "{{route('lead-stage-setting.update', $stage->id)}}";
        }

        $.easyAjax({
            url: url,
            container: '#editStatus',
            type: "POST",
            blockUI: true,
            disableButton: true,
            buttonSelector: "#save-status",
            data: $('#editStatus').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });

</script>
