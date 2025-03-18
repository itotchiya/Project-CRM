<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />

<style>
    .flexible-timing-field {
        display: none
    }
</style>

<div class="modal-header">
    <h5 class="modal-title">@lang('app.addNewShift')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>

<div class="modal-body">
    <div class="portlet-body">
        <x-form id="createTicket" method="POST" class="ajax-form">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100"
                                for="usr">@lang('modules.attendance.shiftType')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="shiftType-strict" :fieldLabel="__('modules.attendance.strictTiming')" fieldName="shift_type"
                                    fieldValue="strict" checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="shiftType-flexible" :fieldLabel="__('modules.attendance.flexibleTiming')" fieldValue="flexible"
                                    fieldName="shift_type" ></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="bootstrap-timepicker">
                            <x-forms.text :fieldLabel="__('modules.attendance.shiftName')" :fieldPlaceholder="__('placeholders.shiftName')" fieldName="shift_name"
                                fieldId="shift_name" fieldRequired="true" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <x-forms.text :fieldLabel="__('modules.attendance.shiftShortCode')" fieldName="shift_short_code" fieldId="shift_short_code"
                            :fieldPlaceholder="__('placeholders.shiftShortCode')" fieldRequired="true" />
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="colorselector" fieldRequired="true" :fieldLabel="__('modules.sticky.colors')">
                            </x-forms.label>
                            <x-forms.input-group id="colorpicker">
                                <input type="text" class="form-control height-35 f-14"
                                    placeholder="{{ __('placeholders.colorPicker') }}" name="color"
                                    id="colorselector">

                                <x-slot name="append">
                                    <span class="input-group-text height-35 colorpicker-input-addon"><i></i></span>
                                </x-slot>
                            </x-forms.input-group>
                        </div>
                    </div>

                    <div class="col-lg-4 strict-timing-field">
                        <div class="bootstrap-timepicker">
                            <x-forms.text :fieldLabel="__('modules.attendance.officeStartTime')" :fieldPlaceholder="__('placeholders.hours')" fieldName="office_start_time"
                                fieldId="office_start_time" fieldRequired="true" />
                        </div>
                    </div>

                    <div class="col-lg-4 strict-timing-field">
                        <div class="bootstrap-timepicker">
                            <x-forms.text :fieldLabel="__('modules.attendance.officeEndTime')" :fieldPlaceholder="__('placeholders.hours')" fieldName="office_end_time"
                                fieldId="office_end_time" fieldRequired="true" />
                        </div>
                    </div>

                    <div class="col-lg-4 flexible-timing-field">
                        <x-forms.number :fieldLabel="__('modules.attendance.totalShiftHours')"  fieldName="total_shift_hours"
                                          fieldId="total_shift_hours" fieldRequired="true" minValue="1" maxValue="23" :popover="__('messages.totalShiftHours')" />
                    </div>

                    <div class="col-lg-4 flexible-timing-field">
                        <x-forms.number :fieldLabel="__('modules.attendance.halfdayShiftHours')"  fieldName="halfday_shift_hours"
                                          fieldId="halfday_shift_hours" fieldRequired="true" minValue="1" maxValue="23" :popover="__('messages.halfdayShiftHours')" />
                    </div>

                    <div class="col-lg-4 flexible-timing-field">
                        <x-forms.number :fieldLabel="__('modules.attendance.autoClockOutTIme')" fieldName="auto_clockout"
                                          fieldId="auto_clockout" fieldRequired="true" minValue="0" fieldValue="1" :popover="__('messages.flexibleAutoClockOut')" />
                    </div>

                    <div class="col-lg-4 strict-timing-field">
                            <x-forms.number :fieldLabel="__('modules.attendance.autoClockOutTIme')" fieldName="auto_clock_out_time"
                                          fieldId="auto_clock_out_time" fieldRequired="true" minValue="0" maxValue="12" fieldValue="1" :popover="__('messages.autoClockOut')" />
                    </div>

                    <div class="col-lg-3 strict-timing-field">
                        <div class="bootstrap-timepicker">
                            <x-forms.text :fieldLabel="__('modules.attendance.halfDayMarkTime')" :fieldPlaceholder="__('placeholders.hours')" fieldName="halfday_mark_time"
                                fieldId="halfday_mark_time" fieldRequired="true" />
                        </div>
                    </div>

                    <div class="col-lg-3 strict-timing-field">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" fieldName="early_clock_in" fieldId="early_clock_in"
                        :fieldLabel="__('modules.attendance.earlyClockIn')"/>
                    </div>

                    <div class="col-lg-3 strict-timing-field">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.attendance.lateMark')" fieldName="late_mark_duration"
                            fieldId="late_mark_duration" fieldRequired="true" />
                    </div>

                    <div class="col-lg-3 strict-timing-field">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.attendance.checkininday')" fieldName="clockin_in_day"
                            fieldId="clockin_in_day" fieldValue="1" fieldRequired="true" />
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="office_open_days" :fieldLabel="__('modules.attendance.officeOpenDays')" fieldRequired="true">
                            </x-forms.label>
                            <div class="d-lg-flex d-sm-block justify-content-between ">

                                <x-forms.weeks fieldName="office_open_days[]"></x-forms.weeks>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-employee-shift" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('#colorpicker').colorpicker({
        "color": "#16813D"
    });

    $('#office_end_time, #office_start_time, #halfday_mark_time').timepicker({
        @if (company()->time_format == 'H:i')
            showMeridian: false,
        @endif
    });

    // save type
    $('#save-employee-shift').click(function() {
        $.easyAjax({
            url: "{{ route('employee-shifts.store') }}",
            container: '#createTicket',
            type: "POST",
            blockUI: '#save-employee-shift',
            disableButton: true,
            buttonSelector: '#save-signature',
            data: $('#createTicket').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });

    setTimeout(function(){
        $('[data-toggle="popover"]').popover();
    }, 300);

    $('input[type=radio][name=shift_type]').change(function() {
        if (this.value == 'flexible') {
            $('.strict-timing-field').hide();
            $('.flexible-timing-field').show();
        } else {
            $('.strict-timing-field').show();
            $('.flexible-timing-field').hide();
        }
    });
</script>
