<div class="row">
    <div class="col-sm-12">
        <x-form id="save-holiday-data-form" method="post">
            <div class="bg-white rounded add-client">
                <h4 class="p-20 mb-0 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.addHoliday')</h4>
                <input type="hidden" name="redirect_url" value="{{ $redirectUrl }}">
                <div class="pt-20 pl-20 pr-20 row">
                    <div class="col-lg-5">
                        <x-forms.text class="date-picker" :fieldLabel="__('app.date')" fieldName="date[]"
                            fieldId="dateField1" :fieldPlaceholder="__('app.date')" fieldValue="{{ $date }}"
                            fieldRequired="true" />
                    </div>
                    <div class="col-lg-5">
                        <div class="my-3 form-group">
                            <x-forms.text :fieldLabel="__('modules.holiday.occasion')" fieldName="occassion[]"
                                fieldId="occassion1" :fieldPlaceholder="__('modules.holiday.occasion')" fieldValue=""
                                fieldRequired="true" />
                        </div>
                    </div>
                    <input type="hidden" name="notification_sent" value="yes">


                </div>

                <div id="insertBefore"></div>

                <!--  ADD ITEM START-->
                <div class="px-3 pt-0 pb-3 mt-2  row px-lg-4 px-md-4">
                    <div class="col-md-12">
                        <a class="f-15 f-w-500" href="javascript:;" id="add-item"><i
                                class="mr-1 icons icon-plus font-weight-bold"></i> @lang('app.add')</a>
                    </div>
                </div>
                <!--  ADD ITEM END-->
                <div class="pt-20 pl-20 pr-20 row">
                <div class="col-lg-5">
                    <x-forms.label class="my-3" fieldId="employee_department"
                    :fieldLabel="__('app.department')" >
                </x-forms.label>
                <x-forms.input-group>
                    <select class="form-control select-picker" name="department[]"
                        id="employee_department" data-live-search="true" multiple>

                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                        @endforeach
                    </select>
                </x-forms.input-group>
                </div>

                <div class="col-lg-5">

                        <x-forms.label class="my-3" fieldId="employee_designation"
                        :fieldLabel="__('app.designation')" >
                    </x-forms.label>
                    <x-forms.input-group>
                        <select class="form-control select-picker" name="designation[]"
                            id="employee_designation" data-live-search="true" multiple data-size="5">

                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </x-forms.input-group>

                </div>

                <div class="col-lg-5 mb-3">
                    <x-forms.select fieldId="employment_type" :fieldLabel="__('modules.employees.employmentType')"
                    fieldName="employment_type[]" :fieldPlaceholder="__('placeholders.date')" multiple data-size="5">

                    <option value="full_time">@lang('app.fullTime')</option>
                    <option value="part_time">@lang('app.partTime')</option>
                    <option value="on_contract">@lang('app.onContract')</option>
                    <option value="internship">@lang('app.internship')</option>
                    <option value="trainee">@lang('app.trainee')</option>
                </x-forms.select>
                </div>
            </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-holiday-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('holidays.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        var $insertBefore = $('#insertBefore');
        var i = 1;

        // Add More Inputs
        $('#add-item').click(function() {
            i += 1;

            $(`<div id="addMoreBox${i}" class="clearfix pl-20 pr-20 row">
                <div class="col-lg-5 col-md-6 col-12"> <x-forms.text class="date-picker" :fieldLabel="__('app.date')" fieldName="date[]"
                fieldId="dateField${i}" :fieldPlaceholder="__('app.date')" fieldValue="{{ $date }}" fieldRequired="true"  />
                </div>  <div class="col-lg-5 col-md-5 col-10"> <div class="my-3 form-group">
                <x-forms.text :fieldLabel="__('modules.holiday.occasion')" fieldName="occassion[]" fieldId="occassion${i}" :fieldPlaceholder="__('modules.holiday.occasion')" fieldValue="" fieldRequired="true" />
                </div> </div> <div class="col-lg-2 col-md-1 col-2"><a href="javascript:;" class="mt-5 d-flex align-items-center justify-content-center remove-item" data-item-id="${i}"><i class="fa fa-times-circle f-20 text-lightest"></i></a></div> </div> `)
                .insertBefore($insertBefore);


            // Recently Added date picker assign
            datepicker('#dateField' + i, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        // Remove fields
        $('body').on('click', '.remove-item', function() {
            var index = $(this).data('item-id');
            $('#addMoreBox' + index).remove();
        });

        const dp1 = datepicker('#dateField1', {
            position: 'bl',
            ...datepickerConfig
        });

        $("#employee_department").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $("#employee_designation").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $("#employment_type").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $('#save-holiday-form').click(function() {

            const url = "{{ route('holidays.store') }}";
            $.easyAjax({
                url: url,
                container: '#save-holiday-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-holiday-form",
                data: $('#save-holiday-data-form').serialize(),
                success: function(response) {
                    window.location.href = response.redirectUrl;
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
