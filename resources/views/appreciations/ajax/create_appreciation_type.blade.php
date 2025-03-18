@php
    $manageAppreciationPermission = user()->permission('manage_award');
@endphp
<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />
<!-- Bootstrap-Iconpicker -->
<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.appreciations.addAppreciationType')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createAppreciationType">
        <div class="row ">
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text fieldId="title" :fieldLabel="__('app.title')"
                                      fieldName="title" fieldRequired="true" :fieldPlaceholder="__('placeholders.appreciation.title')">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.label class="mt-3" fieldId="icon"
                                       :fieldLabel="__('modules.appreciations.chooseIcon')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="icon" id="icon"
                                    data-live-search="true">
                                <option value="">--</option>
                                @foreach ($icons as $item)
                                    <option data-icon="{{ $item->icon }}" data-content="<i class='bi bi-{{ $item->icon }}'></i> {{ $item->title }}" value="{{ $item->id }}">
                                        {{ $item->title }}
                                    </option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="colorselector" fieldRequired="true"
                                           :fieldLabel="__('modules.sticky.colors')">
                            </x-forms.label>
                            <x-forms.input-group id="colorpicker">
                                <input type="text" class="form-control height-35 f-14"
                                       placeholder="{{ __('placeholders.colorPicker') }}" name="color_code" id="colorselector">

                                <x-slot name="append">
                                    <span class="input-group-text height-35 colorpicker-input-addon"><i></i></span>
                                </x-slot>
                            </x-forms.input-group>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="position-relative icon-preview d-flex d-none mt-5">

                        </div>
                    </div>

                    <div class="col-sm-12">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                          :fieldLabel="__('modules.contracts.summery')" fieldName="summery"
                                          fieldId="summery">
                        </x-forms.textarea>
                    </div>
                </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-appreciationType" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(document).ready(function() {
        $("#icon").selectpicker();

        $('#colorpicker').colorpicker({
            "color": "#FF0000"
        });

        $('#icon, #colorselector').on('change', function(e) {
            var iconData = $('#icon').find(':selected').data('icon');

            var color = $('#colorselector').val();

            $('.icon-preview').show();

            var iconDataBackground = `<span class="align-items-center d-inline-flex height-40 justify-content-center rounded width-40" style="background-color: ${color}20;">
                    <i class="bi bi-${iconData} f-15 text-white appreciation-icon" style="color: ${color}  !important"></i>
                </span>`;

            $('.icon-preview').html(iconDataBackground);
        });

        $('#save-appreciationType').click(function() {
            var url = "{{ route('awards.quick-store') }}";
            $.easyAjax({
                url: url,
                container: '#createAppreciationType',
                type: "POST",
                data: $('#createAppreciationType').serialize(),
                success: function(response) {
                    $('#appreciation_type').html(response.data);
                    $('#appreciation_type').selectpicker('refresh');
                    $(MODAL_LG).modal('hide');
                }
            })
        });
    });
</script>
