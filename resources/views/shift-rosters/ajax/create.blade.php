<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-datepicker3.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-attendance-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.addShiftRoster')</h4>
                <div class="row p-20">

                    <div class="col-md-12">
                        <x-alert type="info" icon="info-circle">@lang('messages.existingShiftOverride')</x-alert>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="department_id" :fieldLabel="__('app.department')" fieldName="department_id"
                            search="true">
                            <option value="0">--</option>
                            @foreach ($departments as $team)
                                <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-9">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="selectEmployee" :fieldLabel="__('app.menu.employees')" fieldRequired="true">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="user_id[]" id="selectEmployee"
                                    data-live-search="true" data-size="8">
                                    @foreach ($employees as $item)
                                        <x-user-option :user="$item" :pill="true" />
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    </div>
                </div>
                <div class="row px-4 pb-4">

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="shift" :fieldLabel="__('modules.attendance.shift')" fieldName="shift" search="true">
                            @foreach ($employeeShifts as $item)
                                @if ($item->shift_type == 'strict')
                                <option
                                data-content="<i class='fa fa-circle mr-2' style='color: {{ $item->color }}'></i> {{ ($item->shift_name != 'Day Off') ? $item->shift_name : __('modules.attendance.' . str($item->shift_name)->camel()) }}{{ ($item->shift_name != 'Day Off') ? ' ['.$item->office_start_time.' - '.$item->office_end_time.']' : ''}}"
                                value="{{ $item->id }}">
                                    {{ ($item->shift_name != 'Day Off') ? $item->shift_name : __('modules.attendance.' . str($item->shift_name)->camel()) }}{{ ($item->shift_name != 'Day Off') ? ' ['.$item->office_start_time.' - '.$item->office_end_time.']' : ''}}</option>
                                @else
                                <option data-content="<i class='fa fa-circle mr-2' style='color: {{ $item->color }}'></i> {{ ($item->shift_name != 'Day Off') ? $item->shift_name : __('modules.attendance.' . str($item->shift_name)->camel()) }} {{ ($item->shift_name != 'Day Off') ? ' ['.$item->flexible_total_hours.' '.__('app.hrs').']' : ''}}"
                                    value="{{ $item->id }}">{{ ($item->shift_name != 'Day Off') ? $item->shift_name : __('modules.attendance.' . str($item->shift_name)->camel()) }} {{ ($item->shift_name != 'Day Off') ? ' ['.$item->office_start_time.' - '.$item->office_end_time.']' : ''}}</option>

                                @endif
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="mark_attendance_by_month" :fieldLabel="__('modules.attendance.assignShift') . ' ' . __('app.by')">
                            </x-forms.label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="mark_attendance_by_dates" :fieldLabel="__('app.date')" fieldValue="date"
                                    fieldName="assign_shift_by" checked="true"></x-forms.radio>
                                <x-forms.radio fieldId="duration_multiple" :fieldLabel="__('modules.leaves.multiple')" fieldValue="multiple"
                                    fieldName="assign_shift_by"></x-forms.radio>
                                <x-forms.radio fieldId="mark_attendance_by_month" :fieldLabel="__('app.month')"
                                    fieldName="assign_shift_by" fieldValue="month">
                                </x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 attendance_by_month d-none">
                        <x-forms.select fieldId="year" :fieldLabel="__('app.year')" fieldName="year" search="true"
                            fieldRequired="true">
                            <option value="">--</option>
                            @for ($i = $year+1; $i >= $year - 4; $i--)
                                <option @if ($i == $year) selected @endif value="{{ $i }}">
                                    {{ $i }}</option>
                            @endfor
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6 attendance_by_month d-none">
                        <x-forms.select fieldId="month" :fieldLabel="__('app.month')" fieldName="month" search="true"
                            fieldRequired="true">
                            <x-forms.months :selectedMonth="$month" fieldRequired="true"/>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6 single_date_div">
                        <x-forms.text :fieldLabel="__('messages.selectMultipleDates')" fieldName="single_date" fieldId="single_date" :fieldPlaceholder="__('messages.selectDate')"
                            :fieldValue="Carbon\Carbon::today()->translatedFormat(company()->date_format)" />
                    </div>

                    <div class="col-lg-4 col-md-6 d-none multi_date_div">
                        <input type="hidden" name="startDate" id="startDate">
                        <input type="hidden" name="endDate" id="endDate">
                        <x-forms.text :fieldLabel="__('messages.selectMultipleDates')" fieldName="multi_date" fieldId="multi_date" :fieldPlaceholder="__('messages.selectMultipleDates')"
                            :fieldValue="now(company()->timezone)->translatedFormat(company()->date_format)" />
                    </div>

                    @if ($emailSetting->send_email == 'yes')
                        <div class="col-md-4 mt-3">
                            <x-forms.checkbox :fieldLabel="__('modules.attendance.sendEmail')" fieldName="send_email" fieldId="sendEmail" />
                        </div>
                    @endif

                </div>
                <div class="row pl-20 pr-20 pb-4">
                    <div class="col-sm-12">
                        <x-forms.textarea fieldName="remarks" fieldId="remarks" :fieldLabel="__('app.remark')" />
                    </div>
                </div>
                <div class="row pl-20 pr-20 pb-4">
                    <div class="col-lg-12">
                        <x-forms.file class="mr-0 mr-lg-2 mr-md-2 cropper" :fieldLabel="__('app.menu.addFile')" fieldName="file" fieldId="file" />
                    </div>
                </div>


                <x-form-actions>
                    <x-forms.button-primary class="mr-3" id="save-attendance-form" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('attendances.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script src="{{ asset('vendor/jquery/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/daterangepicker.min.js')}}" defer=""></script>

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
            multidate: true,
            todayHighlight: true,
            locale: {
                format: dateFormat,
                separator: ' To ',
            },
        });

        $('input[type=radio][name=assign_shift_by]').change(function() {
            if (this.value == 'multiple') {
                const dp2 = $('#multi_date').daterangepicker('clearDates').daterangepicker({
                    multidate: true,
                    todayHighlight: true,
                    // format: 'yyyy-mm-d',
                    locale: {
                        format: dateFormat,
                        separator: ' To ',
                    },
                });
            }
        });

        const dp1 = datepicker('#single_date', {
            position: 'bl',
            ...datepickerConfig
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

        $('#save-attendance-form').click(function() {

            var dateRange = $('#multi_date').data('daterangepicker');
            // startDate = dateRange.startDate.format('{{ company()->moment_date_format }}');
            // endDate = dateRange.endDate.format('{{ company()->moment_date_format }}');

            startDate = dateRange.startDate.format('YYYY-MM-DD');
            endDate = dateRange.endDate.format('YYYY-MM-DD');

            var multiDate = [];
            multiDate = [startDate, endDate];
            $('#startDate').val(startDate);
            $('#endDate').val(endDate);
            $('#multi_date').val(multiDate);

            const url = "{{ route('shifts.bulk_shift') }}";

            $.easyAjax({
                url: url,
                container: '#save-attendance-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-attendance-form",
                data: $('#save-attendance-data-form').serialize(),
            });
        });

        $("input[name=assign_shift_by]").click(function() {
            if($(this).val() == 'date'){
                $('.single_date_div').removeClass('d-none')
                $('.multi_date_div').addClass('d-none')
                $('.attendance_by_month').addClass('d-none')
            }
            else if($(this).val() == 'multiple'){
                $('.single_date_div').addClass('d-none')
                $('.multi_date_div').removeClass('d-none')
                $('.attendance_by_month').addClass('d-none')
            }
            else {
                $('.single_date_div').addClass('d-none')
                $('.multi_date_div').addClass('d-none')
                $('.attendance_by_month').removeClass('d-none')
            }
        })

        init(RIGHT_MODAL);
    });
</script>
