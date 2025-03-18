<style>
    .dropdown-item span {
        padding-right: 0;
    }
</style>
@php
    $manageAppreciationTypePermission = user()->permission('manage_award');
@endphp
<div class="row">
    <div class="col-sm-12">
        <x-form id="save-notice-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.appreciations.addAppreciation')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="mt-3" fieldId="appreciation_type"
                                               :fieldLabel="__('modules.appreciations.appreciationType')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="award" id="appreciation_type"
                                            data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($appreciationTypes as $appreciationType)
                                            <option
                                            data-content='<x-award-icon :award="$appreciationType" /> {{ $appreciationType->title }}' value="{{ $appreciationType->id }}">
                                                {{ $appreciationType->title }}
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
                                        <x-user-option :user="$employee" :selected="(!is_null($empID) && $empID == $employee->id)"/>
                                    @endforeach
                                </x-forms.select>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.text :fieldLabel="__('app.date')" fieldName="award_date" fieldId="award_date"
                                        :fieldPlaceholder="__('app.awardDate')" fieldRequired="true"
                                        :fieldValue="now()->translatedFormat(company()->date_format)" />
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <x-forms.label class="my-3" fieldId="summery"
                                        :fieldLabel="__('modules.contracts.summery')">
                                    </x-forms.label>
                                    <div id="summery"></div>
                                    <textarea name="summery" id="summery-text" class="d-none"></textarea>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                              :fieldLabel="__('modules.appreciations.photo')" fieldName="photo" fieldId="photo"
                                              fieldHeight="119" :popover="__('messages.appreciationPhoto')" />
                            </div>

                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-appreciation" class="mr-3" icon="check">@lang('app.save')
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

        quillImageLoad('#summery');

        $('#addAppreciationType').click(function() {
            const url = "{{ route('awards.quick-create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });


        $('#save-appreciation').click(function() {
            const url = "{{ route('appreciations.store') }}";

            var note = document.getElementById('summery').children[0].innerHTML;

            document.getElementById('summery-text').value = note;

            $.easyAjax({
                url: url,
                container: '#save-notice-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-appreciation",
                data: $('#save-notice-data-form').serialize(),
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
