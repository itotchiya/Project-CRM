<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />

@php
    $manageAppreciationTypePermission = user()->permission('manage_appreciation_type');
@endphp
<div class="row">
    <div class="col-sm-12">
        <x-form id="updateAppreciationType" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.appreciations.editAppreciationType')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-6 col-md-6">
                                <x-forms.text fieldId="title" :fieldLabel="__('app.title')"
                                              fieldName="title" fieldRequired="true"
                                              :fieldValue="$appreciationType->title"
                                              :fieldPlaceholder="__('app.title')">
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
                                        <option data-icon="{{ $item->icon }}"  {{ ($appreciationType->award_icon_id == $item->id) ? 'selected' : '' }} data-content="<i class='bi bi-{{ $item->icon }}'></i> {{ $item->title }}" value="{{ $item->id }}">
                                            {{ $item->title }}
                                        </option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>

                            <div class="col-md-4 col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="colorselector" fieldRequired="true"
                                        :fieldLabel="__('modules.awards.backgroundColor')">
                                    </x-forms.label>
                                    <x-forms.input-group id="colorpicker">
                                        <input type="text" class="form-control height-35 f-14"
                                            placeholder="{{ __('placeholders.colorPicker') }}" name="color_code" id="colorselector" value="{{ $appreciationType->color_code }}">

                                        <x-slot name="append">
                                            <span class="input-group-text height-35 colorpicker-input-addon"><i></i></span>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="position-relative icon-preview d-flex d-none mt-5">

                                </div>
                            </div>

                            <div class="col-md-6">
                                <x-forms.select fieldId="status" :fieldLabel="__('app.status')"
                                                fieldName="status">
                                    <option @selected($appreciationType->status == 'active') value="active">@lang('app.active')</option>
                                    <option @selected($appreciationType->status == 'inactive')  value="inactive">@lang('app.inactive')</option>
                                </x-forms.select>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group my-3">
                                    <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                                      :fieldLabel="__('modules.contracts.summery')" fieldName="summery"
                                                      fieldId="summery"
                                                      :fieldValue="$appreciationType->summary ?? ''">
                                    </x-forms.textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-appreciation-type" class="mr-3" icon="check">@lang('app.save')
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
            "color": "{{ $appreciationType->color_code }}"
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

        $('#save-appreciation-type').click(function() {
            const url = "{{ route('awards.update', $appreciationType->id) }}";

            $.easyAjax({
                url: url,
                container: '#updateAppreciationType',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-appreciation-type",
                data: $('#updateAppreciationType').serialize(),
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
            });
        });

        showIconPreview();

        init(RIGHT_MODAL);
    });
</script>
