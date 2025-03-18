<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />

@php
    $manageAppreciationTypePermission = user()->permission('manage_award');
@endphp
<div class="row">
    <div class="col-sm-12">
        <x-form id="createAppreciationType">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.appreciations.addAppreciationType')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-xl-5">
                                <x-forms.text fieldId="title" :fieldLabel="__('app.title')"
                                              fieldName="title" fieldRequired="true" :fieldPlaceholder="__('placeholders.appreciation.title')">
                                </x-forms.text>
                            </div>
                            <div class="col-lg-6 col-md-6 col-xl-3">
                                <x-forms.label class="mt-3" fieldId="icon"
                                               :fieldLabel="__('modules.appreciations.chooseIcon')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="icon" id="icon"
                                            data-live-search="true">
                                        @foreach ($icons as $item)
                                            <option data-icon="{{ $item->icon }}" data-content="<i class='bi bi-{{ $item->icon }}'></i> {{ $item->title }}" value="{{ $item->id }}">
                                                {{ $item->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>

                            <div class="col-md-6 col-lg-4 col-xl-3">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="colorselector" fieldRequired="true"
                                        :fieldLabel="__('modules.awards.backgroundColor')">
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

                            <div class="col-sm-6 col-xl-1 text-right">
                                <div class="position-relative icon-preview d-flex d-none mt-5">

                                </div>
                            </div>
                            <div class="col-sm-12">
                                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                                  :fieldLabel="__('modules.contracts.summery')" fieldName="summery"
                                                  fieldId="summery"
                                                  :fieldValue="$appreciationType->summary ?? ''">
                                </x-forms.textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-appreciationType" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('appreciations.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#colorpicker').colorpicker({
            "color": "#FF0000"
        });

        $('#icon, #colorselector').on('change', function(e) {
           showIconPreview();
        });

        function showIconPreview() {
            var iconData = $('#icon').find(':selected').data('icon');

            var color = $('#colorselector').val();

            $('.icon-preview').show();
            var iconDataBackground = `<span class="align-items-center d-inline-flex height-40 justify-content-center rounded width-40" style="background-color: ${color}20;">
                    <i class="bi bi-${iconData} f-15 text-white appreciation-icon" style="color: ${color}  !important"></i>
                </span>`;

            $('.icon-preview').html(iconDataBackground);
        }

        $('#save-appreciationType').click(function() {
            var url = "{{ route('awards.store') }}";
            $.easyAjax({
                url: url,
                container: '#createAppreciationType',
                type: "POST",
                blockUI: true,
                disableButton: true,
                buttonSelector: '#save-appreciationType',
                data: $('#createAppreciationType').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            })
        });

        showIconPreview();

        init(RIGHT_MODAL);
    });
</script>
