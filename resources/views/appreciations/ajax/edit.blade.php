@php
    $manageAppreciationTypePermission = user()->permission('manage_award');
@endphp
<div class="row">
    <div class="col-sm-12">
        <x-form id="update-appreciation-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.appreciations.editAppreciation')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId="appreciation_type"
                                               :fieldLabel="__('modules.appreciations.appreciationType')">
                                </x-forms.label>
                                <x-forms.input-group>

                                    <select class="form-control select-picker" name="award" id="appreciation_type"
                                            data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($appreciationTypes as $appreciationType)
                                            <option @if($appreciation->award_id == $appreciationType->id) selected @endif  data-content="<i class='bi bi-{{ $appreciationType->awardIcon->icon }}' style='color: {{ $appreciationType->color_code }}'></i> {{ $appreciationType->title }}" value="{{ $appreciationType->id }}">
                                                {{ $appreciationType->title }} {{ $appreciationType->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($manageAppreciationTypePermission == 'all')
                                        <x-slot name="append">
                                            <button id="addAppreciationType" type="button"
                                                    class="btn btn-outline-secondary border-grey"
                                                    data-toggle="tooltip" data-original-title="{{ __('modules.appreciations.addAppreciationType') }}">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.select fieldId="award_to" :fieldLabel="__('modules.appreciations.awardTo')"
                                                fieldName="given_to" search="true" fieldRequired="true">
                                    <option value="">--</option>
                                    @foreach ($employees as $employee)
                                        <x-user-option :user="$employee" :selected="($appreciation->award_to == $employee->id)"/>
                                    @endforeach
                                </x-forms.select>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.text :fieldLabel="__('app.date')" fieldName="award_date" fieldId="award_date"
                                              :fieldPlaceholder="__('app.awardDate')"
                                              :fieldValue="$appreciation->award_date->translatedFormat(company()->date_format)" />
                            </div>

                            <div class="col-md-12">
                                <div class="form-group my-3">
                                    <x-forms.label class="my-3" fieldId="summery"
                                                   :fieldLabel="__('modules.contracts.summery')">
                                    </x-forms.label>
                                    <div id="summery">{!! $appreciation->summary !!}</div>
                                    <textarea name="summery" id="summery-text" class="d-none"></textarea>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                              :fieldLabel="__('modules.appreciations.photo')" fieldName="photo" fieldId="photo"
                                              :fieldValue="($appreciation->image ? $appreciation->image_url : '')"
                                              fieldHeight="119" :popover="__('messages.appreciationPhoto')" />
                            </div>

                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-appreciation" class="mr-3" icon="check">@lang('app.save')
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

        const dp1 = datepicker('#award_date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#addAppreciationType').click(function() {
            const url = "{{ route('awards.quick-create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        quillImageLoad('#summery');

        $('#update-appreciation').click(function() {
            const url = "{{ route('appreciations.update', $appreciation->id) }}";

            var note = document.getElementById('summery').children[0].innerHTML;

            document.getElementById('summery-text').value = note;

            $.easyAjax({
                url: url,
                container: '#update-appreciation-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-appreciation",
                data: $('#update-appreciation-data-form').serialize(),
                file: true,
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

        init(RIGHT_MODAL);
    });
</script>
