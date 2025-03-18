<div class="row">
    <div class="col-sm-12">
        <x-form id="save-holiday-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.editHoliday')</h4>
                <div class="row p-20">

                    <div class="col-lg-6">
                        <x-forms.text :fieldLabel="__('app.date')" fieldName="date" fieldId="date" :fieldPlaceholder="__('app.date')"
                            :fieldValue="$holiday->date->translatedFormat(company()->date_format)" />
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group my-3">
                            <x-forms.text :fieldLabel="__('modules.holiday.occasion')" fieldName="occassion" fieldId="occassion" :fieldPlaceholder="__('modules.holiday.occasion')"
                                :fieldValue="$holiday->occassion" fieldRequired="true" />
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <x-forms.label class="my-3" fieldId="employee_department" :fieldLabel="__('app.department')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="department[]" id="employee_department"
                                data-live-search="true" multiple data-size="6">

                                @foreach ($teams as $team)
                                <option {{ in_array($team->id, $departmentArray) ? 'selected' : '' }} value="{{ $team->id }}">
                                    {{ $team->team_name }}</option>
                            @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-6">
                        <x-forms.label class="my-3" fieldId="employee_designation" :fieldLabel="__('app.designation')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="designation[]" id="employee_designation"
                                data-live-search="true" multiple data-size="4">

                                @foreach ($designations as $designation)
                                    <option {{ in_array($designation->id, $designationArray) ? 'selected' : '' }} value="{{ $designation->id }}">
                                        {{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-6">
                        <x-forms.select fieldId="employment_type" :fieldLabel="__('modules.employees.employmentType')" fieldName="employment_type[]"
                            :fieldPlaceholder="__('placeholders.date')" multiple data-size="5">

                            <option value="full_time" @if (is_array($employmentTypeArray) && in_array('full_time', $employmentTypeArray)) selected @endif>
                                @lang('app.fullTime')</option>
                            <option value="part_time" @if (is_array($employmentTypeArray) && in_array('part_time', $employmentTypeArray)) selected @endif>
                                @lang('app.partTime')</option>
                            <option value="on_contract" @if (is_array($employmentTypeArray) && in_array('on_contract', $employmentTypeArray)) selected @endif>
                                @lang('app.onContract')</option>
                            <option value="internship" @if (is_array($employmentTypeArray) && in_array('internship', $employmentTypeArray)) selected @endif>
                                @lang('app.internship')</option>
                            <option value="trainee" @if (is_array($employmentTypeArray) && in_array('trainee', $employmentTypeArray)) selected @endif>@lang('app.trainee')
                            </option>
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

        $("#employee_department").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $("#employee_designation").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $("#employment_type").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        const dp1 = datepicker('#date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $holiday->date) }}"),
            ...datepickerConfig
        });

        $('#save-holiday-form').click(function() {

            const url = "{{ route('holidays.update', $holiday->id) }}";

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
