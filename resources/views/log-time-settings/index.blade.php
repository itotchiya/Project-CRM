@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                <div class="row">
                    <div class="col-lg-6 mb-2">
                        <x-forms.toggle-switch :fieldLabel="__('modules.logTimeSetting.autoStopTimerAfterOfficeTime')"
                            fieldName="" fieldId="auto_timer_stop" fieldValue="yes"
                            :checked="$logTime->auto_timer_stop == 'yes'" />
                    </div>
                    <div class="col-lg-6 mb-2">
                        <x-forms.toggle-switch :fieldLabel="__('modules.logTimeSetting.approvalRequired')"
                            fieldName="" fieldId="approval_required" fieldValue="true"
                            :checked="$logTime->approval_required" />
                    </div>
                    <div class="col-lg-6 mb-2">
                        <x-forms.toggle-switch :fieldLabel="__('modules.logTimeSetting.trackerReminder')"
                            fieldName="" fieldId="tracker_reminder" fieldValue="true"
                            :checked="$logTime->tracker_reminder" />
                    </div>
                    <div class="col-md-3 col-lg-3 @if($logTime->tracker_reminder == 0) d-none @endif" id="timepicker">
                        <div class="bootstrap-timepicker timepicker">
                            <x-forms.text :fieldLabel="__('app.time')"
                                :fieldPlaceholder="__('placeholders.hours')" fieldName=""
                                fieldId="time" fieldRequired="true" :fieldValue="$time"/>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <x-forms.toggle-switch :fieldLabel="__('modules.logTimeSetting.dailyTimelogReport')"
                            fieldName="" fieldId="timelog_report" fieldValue="true"
                            :checked="$logTime->timelog_report" />
                    </div>
                    <div class="col-lg-12 @if ($logTime->timelog_report == 0) d-none @endif " id="daily-report-roles">
                        <x-forms.select :fieldLabel="__('modules.attendance.chooseRoleReport')" fieldName="daily_report_roles[]" fieldId="daily_report_roles"
                        fieldRequired="true" multiple="true">
                            @foreach ($roles as $item)
                                <option
                                @if (is_array($dailyReportRoles) && in_array($item->id, $dailyReportRoles))
                                    selected
                                @endif
                                value="{{ $item->id }}">{{ $item->display_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>
            </div>
            <x-slot name="action">
                <!-- Buttons Start -->
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
                <!-- Buttons End -->
            </x-slot>

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#save-form').click(function() {
                var auto_timer_stop = 'no';
                var approval_required = 0;
                var tracker_reminder = 0;
                var timelog_report = 0;

                if ($('#auto_timer_stop').prop("checked") == true) {
                    auto_timer_stop = 'yes';
                }
                if ($('#approval_required').prop("checked") == true) {
                    approval_required = 1;
                }
                if ($('#tracker_reminder').prop("checked") == true) {
                    tracker_reminder = 1;
                }
                if ($('#timelog_report').prop("checked") == true) {
                    timelog_report = 1;
                }
                var dailyReport = $('#daily-report-roles option:selected').map(function(){
                return this.value;
                }).get();

                var time = $('#time').val();

                $.easyAjax({
                    url: "{{ route('timelog-settings.store') }}",
                    blockUI: true,
                    type: "POST",
                    data: {
                        'auto_timer_stop': auto_timer_stop,
                        'approval_required': approval_required,
                        'tracker_reminder': tracker_reminder,
                        'timelog_report': timelog_report,
                        'daily_report_roles' : dailyReport,
                        'time': time,
                        '_token': "{{ csrf_token() }}"
                    }
                })
            });



            $('#time').timepicker({
                @if (company()->time_format == 'H:i')
                    showMeridian: false,
                @endif
            });

            $('#tracker_reminder').change(function() {
                $('#timepicker').toggleClass('d-none');
            });

            $('#timelog_report').click(function() {
                $('#daily-report-roles').toggleClass('d-none');
            });

        });
    </script>
@endpush
