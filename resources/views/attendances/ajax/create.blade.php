<link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">


<div class="row">
    <div class="col-sm-12">
        <x-form id="save-attendance-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                        @lang('app.attendanceDetails')</h4>
                <div class="row p-20">

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="department_id" :fieldLabel="__('app.department')"
                            fieldName="department_id" search="true">
                            <option value="0">--</option>
                            @foreach ($departments as $team)
                                <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-9">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="selectEmployee" :fieldLabel="__('app.menu.employees')"
                                fieldRequired="true">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="user_id[]"
                                    id="selectEmployee" data-live-search="true" data-size="8">
                                    @foreach ($employees as $item)
                                        <x-user-option :user="$item" :pill="true"/>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    </div>

                </div>
                <div class="row px-4 pb-4">

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="location" :fieldLabel="__('app.location')" fieldName="location"
                        search="true">
                            @foreach ($location as $locations)
                                <option @if ($locations->is_default == 1) selected @endif value="{{ $locations->id }}">
                                    {{ $locations->location }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="mark_attendance_by_month" :fieldLabel="__('modules.attendance.markAttendance'). ' ' . __('app.by')">
                            </x-forms.label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="mark_attendance_by_month" :fieldLabel="__('app.month')" fieldName="mark_attendance_by"
                                    fieldValue="month" checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="mark_attendance_by_dates" :fieldLabel="__('app.date')" fieldValue="date"
                                    fieldName="mark_attendance_by"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 attendance_by_month">
                        <x-forms.select fieldId="year" :fieldLabel="__('app.year')" fieldName="year" search="true"
                            fieldRequired="true">
                            <option value="">--</option>
                            @for ($i = $year; $i >= $year - 4; $i--)
                                <option @if ($i == $year) selected @endif
                                    value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6 attendance_by_month">

                        <x-forms.select fieldId="month" :fieldLabel="__('app.month')" fieldName="month" search="true"
                            fieldRequired="true">
                            <x-forms.months :selectedMonth="$month" fieldRequired="true"/>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6 d-none multi_date_div">
                        <x-forms.text :fieldLabel="__('messages.selectMultipleDates')" fieldName="multi_date"
                            fieldId="multi_date" :fieldPlaceholder="__('messages.selectMultipleDates')"
                            :fieldValue="Carbon\Carbon::today()->translatedFormat(company()->date_format)" />
                    </div>

                </div>
                <div class="row px-4">
                    <div class="col-lg-4 col-md-6 col-xl-3">
                        <div class="bootstrap-timepicker timepicker">
                            <x-forms.text :fieldLabel="__('modules.attendance.clock_in')"
                                :fieldPlaceholder="__('placeholders.hours')" fieldName="clock_in_time"
                                fieldId="start_time" fieldRequired="true"
                                :fieldValue="\Carbon\Carbon::createFromFormat('H:i:s', attendance_setting()->shift->office_start_time)->format(company()->time_format)" />
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-xl-3">
                        <div class="bootstrap-timepicker timepicker">
                            <x-forms.text :fieldLabel="__('modules.attendance.clock_out')"
                                :fieldPlaceholder="__('placeholders.hours')" fieldName="clock_out_time"
                                fieldId="end_time"
                                :fieldValue="\Carbon\Carbon::createFromFormat('H:i:s', attendance_setting()->shift->office_end_time)->format(company()->time_format)" />
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-xl-3">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="late_yes" :fieldLabel="__('modules.attendance.late')">
                            </x-forms.label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="late_yes" :fieldLabel="__('app.yes')" fieldName="late"
                                    fieldValue="yes">
                                </x-forms.radio>
                                <x-forms.radio fieldId="late_no" :fieldLabel="__('app.no')" fieldValue="no"
                                    fieldName="late" checked="true"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-xl-3">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="half_day_yes" :fieldLabel="__('modules.attendance.halfDay')">
                            </x-forms.label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="half_day_yes" :fieldLabel="__('app.yes')" fieldName="half_day"
                                    fieldValue="yes">
                                </x-forms.radio>
                                <x-forms.radio fieldId="half_day_no" :fieldLabel="__('app.no')" fieldValue="no"
                                    fieldName="half_day" checked="true"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-xl-3" id="half_day_section" style="display: none;">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="duration" :fieldLabel="__('modules.leaves.selectDuration')">
                            </x-forms.label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="first_half_day_yes" :fieldLabel="__('modules.leaves.firstHalf')" fieldName="half_day_duration"
                                    fieldValue="first_half" checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="first_half_day_no" :fieldLabel="__('modules.leaves.secondHalf')" fieldValue="second_half"
                                    fieldName="half_day_duration"></x-forms.radio>
                            </div>
                        </div>
                    </div>


                </div>

                <div class="row p-20">
                    <div class="col-lg-3 col-md-3">
                        <x-forms.select fieldId="work_from_type" :fieldLabel="__('modules.attendance.working_from')" fieldName="work_from_type" fieldRequired="true"
                            search="true" >
                                <option value="office">@lang('modules.attendance.office')</option>
                                <option value="home">@lang('modules.attendance.home')</option>
                                <option value="other">@lang('modules.attendance.other')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6" id="other_place" style="display:none">
                        <x-forms.text fieldId="working_from" :fieldLabel="__('modules.attendance.otherPlace')" fieldName="working_from" fieldRequired="true" >
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4 col-md-6 mt-5">
                        <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.overwriteAttendance')"
                                          fieldName="overwrite_attendance" fieldId="overwrite_attendance" fieldValue="yes"
                                          fieldRequired="true" :popover="__('messages.overwriteAttendanceTooltip')"/>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary class="mr-3" id="save-attendance-form" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('attendances.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}" defer=""></script>
<script>

    $(document).ready(function() {
        $("#selectEmployee").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        var dateFormat = "{{ $dateformat }}";

        $('#multi_date').daterangepicker({
            linkedCalendars: false,
            multidate: true,
            todayHighlight: true,
            // format: 'yyyy-mm-d'
            locale: {
                format: dateFormat,
                separator: ' To ',
            },
        });

        $('input[type=radio][name=mark_attendance_by]').change(function() {
            if(this.value=='date') {
                $('#multi_date').daterangepicker('clearDates').daterangepicker({
                    linkedCalendars: false,
                    multidate: true,
                    todayHighlight: true,
                    // format: 'yyyy-mm-d',
                    locale: {
                        format: dateFormat,
                        separator: ' To ',
                    },
                    maxDate: new Date(),
                });
            }

        });
        $('#work_from_type').change(function(){
            ($(this).val() == 'other') ? $('#other_place').show() : $('#other_place').hide();
        });

        $('#start_time, #end_time').timepicker({
            showMeridian: (company.time_format == 'H:i' ? false : true)
        });

        $('#department_id').change(function() {
            var id = $(this).val();
            var url = "{{ route('employees.by_department', ':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                url: url,
                container: '#save-attendance-data-form',
                type: "GET",
                blockUI: true,
                data: $('#save-attendance-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        $('#selectEmployee').html(response.data);
                        $('#selectEmployee').selectpicker('refresh');
                    }
                }
            });
        });

        const saveAttendanceForm = () => {

            var dateRange = $('#multi_date').data('daterangepicker');
            startDate = dateRange.startDate.format('{{ company()->moment_date_format }}');
            endDate = dateRange.endDate.format('{{ company()->moment_date_format }}');
            var multiDate = [];
            multiDate = [startDate, endDate];
            $('#multi_date').val(multiDate);
            const url = "{{ route('attendances.bulk_mark')}}";

            $.easyAjax({
                url: url,
                container: '#save-attendance-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-attendance-form",
                data: $('#save-attendance-data-form').serialize()
            });
        };

        $('#save-attendance-form').click(function()
        {
            var dateRange = $('#multi_date').data('daterangepicker');
            startDate = dateRange.startDate.format('{{ company()->moment_date_format }}');
            endDate = dateRange.endDate.format('{{ company()->moment_date_format }}');
            var multiDate = [];
            multiDate = [startDate, endDate];
            $('#multi_date').val(multiDate);
            var url = "{{ route('attendances.check_half_day') }}?type=bulkMark";

            $.easyAjax({
                url: url,
                container: '#save-attendance-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-attendance-form",
                data: $('#save-attendance-data-form').serialize(),
                success: function(response) {
                        if ((response.halfDayExist == true && response.requestedHalfDay == 'no') &&
                        response.halfDayDurEnd == 'no' &&
                        (response.fullDayExist == false && response.requestedFullDay == 'yes')) {
                            Swal.fire({
                                title: "@lang('messages.sweetAlertTitle')",
                                text: "@lang('messages.halfDayAlreadyApplied')",
                                icon: 'warning',
                                showCancelButton: true,
                                focusConfirm: false,
                                confirmButtonText: "@lang('messages.rejectIt')",
                                cancelButtonText: "@lang('app.cancel')",
                                customClass: {
                                    confirmButton: 'btn btn-primary mr-3',
                                    cancelButton: 'btn btn-secondary'
                                },
                                showClass: {
                                    popup: 'swal2-noanimation',
                                    backdrop: 'swal2-noanimation'
                                },
                                buttonsStyling: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    saveAttendanceForm();
                                }
                            });

                        } else if ((response.fullDayExist == true && response.requestedFullDay == 'no') &&
                        (response.halfDayExist == false && response.requestedHalfDay == 'yes')) {
                            Swal.fire({
                                title: "@lang('messages.sweetAlertTitle')",
                                text: "@lang('messages.fullDayAlreadyApplied')",
                                icon: 'warning',
                                showCancelButton: true,
                                focusConfirm: false,
                                confirmButtonText: "@lang('messages.rejectIt')",
                                cancelButtonText: "@lang('app.cancel')",
                                customClass: {
                                    confirmButton: 'btn btn-primary mr-3',
                                    cancelButton: 'btn btn-secondary'
                                },
                                showClass: {
                                    popup: 'swal2-noanimation',
                                    backdrop: 'swal2-noanimation'
                                },
                                buttonsStyling: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    saveAttendanceForm();
                                }
                            });
                        } else if((response.halfDayExist == true && response.requestedHalfDay == 'no') &&
                            (response.fullDayExist == true && response.requestedFullDay == 'no')){
                                Swal.fire({
                                title: "@lang('messages.sweetAlertTitle')",
                                text: "@lang('messages.leaveAlreadyApplied')",
                                icon: 'warning',
                                showCancelButton: true,
                                focusConfirm: false,
                                confirmButtonText: "@lang('messages.rejectIt')",
                                cancelButtonText: "@lang('app.cancel')",
                                customClass: {
                                    confirmButton: 'btn btn-primary mr-3',
                                    cancelButton: 'btn btn-secondary'
                                },
                                showClass: {
                                    popup: 'swal2-noanimation',
                                    backdrop: 'swal2-noanimation'
                                },
                                buttonsStyling: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    saveAttendanceForm();
                                }
                            });
                        }else {
                            saveAttendanceForm();
                        }
                }
            });
        });

        $("input[name=mark_attendance_by]").click(function() {
            $(this).val() == 'date' ? $('.multi_date_div').removeClass('d-none') : $(
                '.multi_date_div').addClass('d-none');
            $(this).val() == 'date' ? $('.attendance_by_month').addClass('d-none') : $(
                '.attendance_by_month').removeClass('d-none');
        })

        init(RIGHT_MODAL);


        function toggleHalfDaySection() {

            if ($('input[name="half_day"]:checked').val() === 'yes') {
                $('#half_day_section').show();
            } else {
                $('#half_day_section').hide();
            }
        }

        $('input[name="half_day"]').change(toggleHalfDaySection);


        toggleHalfDaySection();
    });
</script>
