@php
$editAttendancePermission = user()->permission('add_attendance');
$deleteAttendancePermission = user()->permission('delete_attendance');
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">
        @lang('modules.attendance.markAttendance')
    </h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12 mb-4">
            <x-employee :user="$attendanceUser" />
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <h5 class="f-w-500 f-15 d-flex justify-content-between">{{ __('app.date').' - '.\Carbon\Carbon::parse($date)->translatedFormat(company()->date_format) }} <span class="badge badge-info f-14" style="background-color: {{ $attendanceSettings->color }}">{{ $attendanceSettings->shift_name }}</span></h5>

            <x-form id="attendance-container">
                <input type="hidden" name="attendance_date" value="{{ $date }}">

                <div class="row">

                    <div class="col-lg-4 col-md-6">
                        <div class="bootstrap-timepicker timepicker">
                            <x-forms.text class="a-timepicker" :fieldLabel="__('modules.attendance.clock_in')"
                                :fieldPlaceholder="__('placeholders.hours')" fieldName="clock_in_time"
                                fieldId="clock-in-time" fieldRequired="true" />
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text class="a-timepicker" :fieldLabel="__('modules.attendance.clock_in_ip')"
                            :fieldPlaceholder="__('placeholders.hours')" fieldName="clock_in_ip"
                            fieldId="clock-in-ip" :fieldValue="request()->ip()" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.toggle-switch class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.attendance.late')" fieldName="late" fieldId="lateday" />
                    </div>
                </div>

                <div class="row">

                    <div class="col-lg-4 col-md-6">
                        <div class="bootstrap-timepicker timepicker">
                            <x-forms.text :fieldLabel="__('modules.attendance.clock_out')"
                                :fieldPlaceholder="__('placeholders.hours')" fieldName="clock_out_time"
                                fieldId="clock-out" />
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <x-forms.text :fieldLabel="__('modules.attendance.clock_out_ip')"
                            :fieldPlaceholder="__('placeholders.hours')" fieldName="clock_out_ip"
                            fieldId='clock_out_ip' :fieldValue="request()->ip()" />
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <x-forms.toggle-switch class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.attendance.halfDay')" fieldName="halfday"
                            fieldId="halfday" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-6 col-xl-4" id="half_day_section" style="display: none;">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="duration" :fieldLabel="__('modules.leaves.selectDuration')">
                            </x-forms.label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="first_half_day_yes" :fieldLabel="__('modules.leaves.firstHalf')" fieldName="half_day_duration"
                                    fieldValue="first_half"  checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="first_half_day_no" :fieldLabel="__('modules.leaves.secondHalf')" fieldValue="second_half"
                                    fieldName="half_day_duration"></x-forms.radio>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="location" :fieldLabel="__('app.location')" fieldName="location"
                        search="true">
                            @foreach ($location as $locations)
                                <option value="{{ $locations->id }}">
                                    {{ $locations->location }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="work_from_type" :fieldLabel="__('modules.attendance.working_from')" fieldName="work_from_type" fieldRequired="true"
                        search="true" >
                        <option @if ($attendance->work_from_type == 'home') selected @endif value="home">@lang('modules.attendance.home')</option>
                        <option @if ($attendance->work_from_type == 'other') selected @endif value="other">@lang('modules.attendance.other')</option>
                        <option @if ($attendance->work_from_type == 'office') selected @endif value="office">@lang('modules.attendance.office')</option>
                        </x-forms.select>
                    </div>

                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6"  id="otherPlace" @if ($attendance->work_from_type != 'other') style="display:none" @endif >
                        <x-forms.text fieldId="working_from" :fieldLabel="__('modules.attendance.otherPlace')" fieldName="working_from" fieldRequired="true" :fieldValue="$attendance->working_from">
                        </x-forms.text>
                    </div>
                </div>
                <input type="hidden" name="user_id" id="user_id" value="{{ $attendanceUser->id }}">
            </x-form>
        </div>
    </div>

</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-attendance" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('.select-picker').selectpicker();

    $(document).ready(function() {

        if ($('#halfday').is(':checked')) {
            $('#half_day_section').show();
        } else {
            $('#half_day_section').hide();
        }

        $('#halfday').change(function() {
            if ($(this).is(':checked')) {
                $('#half_day_section').show();
            } else {
                $('#half_day_section').hide();
            }
        });

        $('#clock-in-time').timepicker({
            @if(company()->time_format == 'H:i')
            showMeridian: false,
            @endif
            minuteStep: 1
        });
        $('#clock-out').timepicker({
            @if(company()->time_format == 'H:i')
            showMeridian: false,
            @endif
            minuteStep: 1,
            defaultTime: false
        });

        $('#work_from_type').change(function(){
            ($(this).val() == 'other') ? $('#otherPlace').show() : $('#otherPlace').hide();
        });

        $('#save-attendance').click(function () {

            var url = "{{route('attendances.store')}}";
            $.easyAjax({
                url: url,
                type: "POST",
                container: '#attendance-container',
                blockUI: true,
                disableButton: true,
                buttonSelector: "#save-attendance",
                data: $('#attendance-container').serialize(),
                success: function (response) {
                    if(response.status == 'success'){
                        $(MODAL_XL).modal('hide');
                        $(MODAL_LG).modal('hide');
                        showTable();
                    }
                }
            })
        });
    });


</script>
