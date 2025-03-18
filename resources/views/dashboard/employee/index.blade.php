@extends('layouts.app')

@push('styles')
    @if ((!is_null($viewEventPermission) && $viewEventPermission != 'none')
        || (!is_null($viewHolidayPermission) && $viewHolidayPermission != 'none')
        || (!is_null($viewTaskPermission) && $viewTaskPermission != 'none')
        || (!is_null($viewTicketsPermission) && $viewTicketsPermission != 'none')
        || (!is_null($viewLeavePermission) && $viewLeavePermission != 'none')
        )
        <link rel="stylesheet" href="{{ asset('vendor/full-calendar/main.min.css') }}" defer="defer">
    @endif
    <style>
        .h-200 {
            max-height: 340px;
            overflow-y: auto;
        }

        .dashboard-settings {
            width: 600px;
        }

        @media (max-width: 768px) {
            .dashboard-settings {
                width: 300px;
            }
        }

        .fc-list-event-graphic{
            display: none;
        }

        .fc .fc-list-event:hover td{
            background-color: #fff !important;
            color:#000 !important;
        }
        .left-3{
            margin-right: -22px;
        }
        .clockin-right{
            margin-right: -10px;
        }

        .week-pagination li {
            margin-right: 5px;
            z-index: 1;
        }
        .week-pagination li a {
            border-radius: 50%;
            padding: 2px 6px !important;
            font-size: 11px !important;
        }

        .week-pagination li.page-item:first-child .page-link {
            border-top-left-radius: 50%;
            border-bottom-left-radius: 50%;
        }

        .week-pagination li.page-item:last-child .page-link {
            border-top-right-radius: 50%;
            border-bottom-right-radius: 50%;
        }
    </style>
@endpush

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="px-4 py-2 border-top-0 emp-dashboard">
        <!-- WELOCOME START -->
        @if (!is_null($checkTodayLeave))
            <div class="row pt-4">
                <div class="col-md-12">
                    <x-alert type="info" icon="info-circle">
                        <a href="{{ route('leaves.show', $checkTodayLeave->id) }}" class="openRightModal text-dark-grey">
                            <u>@lang('messages.youAreOnLeave')</u>
                        </a>
                    </x-alert>
                </div>
            </div>
        @elseif (!is_null($checkTodayHoliday))
            <div class="row pt-4">
                <div class="col-md-12">
                    <x-alert type="info" icon="info-circle">
                        <a href="{{ route('holidays.show', $checkTodayHoliday->id) }}" class="openRightModal text-dark-grey">
                            <u>@lang('messages.holidayToday')</u>
                        </a>
                    </x-alert>
                </div>
            </div>
        @endif


        @if(in_array('admin', user_roles()))
            <div class="row">
                @include('dashboard.update-message-dashboard')
                @includeIf('dashboard.update-message-module-dashboard')
                <x-cron-message :modal="true"></x-cron-message>
            </div>
        @endif

        <div class="d-lg-flex d-md-flex d-block py-2 pb-2 align-items-center">

            <!-- WELOCOME NAME START -->
            <div>
                <h3 class="heading-h3 mb-0 f-21 font-weight-bold">@lang('app.welcome') {{ $user->name }}</h3>
            </div>
            <!-- WELOCOME NAME END -->

            <!-- CLOCK IN CLOCK OUT START -->
            <div
                class="ml-auto d-flex clock-in-out mb-3 mb-lg-0 mb-md-0 m mt-4 mt-lg-0 mt-md-0 justify-content-end">
                <p
                    class="mb-0 text-lg-right text-md-right f-18 font-weight-bold text-dark-grey d-grid align-items-center">
                    <input type="hidden" id="current-latitude" name="current_latitude">
                    <input type="hidden" id="current-longitude" name="current_longitude">

                    <span id="dashboard-clock">
                        {!! now()->timezone(company()->timezone)->translatedFormat(company()->time_format) . '</span><span class="f-10 font-weight-normal">' . now()->timezone(company()->timezone)->translatedFormat('l') . '</span>' !!}

                    @if (!is_null($currentClockIn))
                        <span class="f-11 font-weight-normal text-lightest">
                            @lang('app.clockInAt') -
                            {{ $currentClockIn->clock_in_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                        </span>
                    @endif
                </p>

                @php
                    $currentDateTime = now()->timezone(company()->timezone);
                    $msg = null;
                    $start_time = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceSettings->office_start_time, company()->timezone);
                    $mid_time = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceSettings->halfday_mark_time, company()->timezone);
                    $end_time = \Carbon\Carbon::createFromFormat('H:i:s', $attendanceSettings->office_end_time, company()->timezone);

                    if ($start_time->gt($end_time)) { // check if shift end time is less then current time then shift not ended yet
                        if(
                            now(company()->timezone)->lessThan($end_time)
                            || (now(company()->timezone)->greaterThan($end_time) && now(company()->timezone)->lessThan($start_time))
                        ){
                            $start_time->subDay();
                            $mid_time->subDay();
                        }else{
                            $mid_time->addDay();
                            $end_time->addDay();
                        }
                    }

                    $current_user_id = auth()->user()->id;
                    $leaveApplied = $leave->where('user_id', $current_user_id)->where('status', 'approved')->first();
                    $shiftAssigned = \App\Models\EmployeeShiftSchedule::where('user_id', $current_user_id)->where('date', $currentDateTime->format('Y-m-d'))->first();

                    $defaultShiftID = attendance_Setting()->default_employee_shift;
                    $defaultShift = \App\Models\EmployeeShift::findOrFail($defaultShiftID);
                    $currentDayOfWeek = $currentDateTime->format('N');
                    $isDefaultShiftAssignedToday = in_array($currentDayOfWeek, json_decode($defaultShift->office_open_days, true));

                    if($leaveApplied != null && ($leaveApplied->duration === 'single' || $leaveApplied->duration === 'multiple') && $currentDateTime->between($start_time, $end_time)){
                        $msg = __('messages.leaveApplied');
                    }elseif ($leaveApplied != null  && $leaveApplied->duration === 'half day' && $leaveApplied->half_day_type === 'first_half' && $currentDateTime->lt($mid_time) && $currentDateTime->between($start_time, $mid_time)){
                        $msg = __('messages.leaveForFirstHalf');
                    }elseif ($leaveApplied != null  && $leaveApplied->duration === 'half day' && $leaveApplied->half_day_type === 'second_half' && $currentDateTime->gt($mid_time) && $currentDateTime->between($mid_time, $end_time)){
                        $msg = __('messages.leaveForSecondHalf');
                    }else if(!is_null($checkTodayHoliday) && $checkTodayHoliday->exists && !$shiftAssigned?->exists){
                        $msg = __('messages.todayHoliday');
                    }else if(!is_null($checkTodayHoliday) && $checkTodayHoliday->exists && $shiftAssigned?->exists && $cannotLogin == true){
                        $msg = __('messages.todayHoliday');
                    }else if($shiftAssigned == null && !$isDefaultShiftAssignedToday){
                        $msg = __('messages.shiftNotAssigned');
                    }else if($cannotLogin == true){
                        $msg = __('messages.notInOfficeHours');
                    }
                    /*
                        true means shift is near to cross with auto clock time adding clock out show
                        false means show clock in
                    */
                    $flagbtn = now()->timezone(company()->timezone)->addHours($attendanceSettings->auto_clock_out_time)->gt($shiftEndDateTime);



                @endphp


                @if (in_array('attendance', user_modules()))
                    @if (is_null($currentClockIn) && $checkJoiningDate == true || (is_null($currentClockIn) && $flagbtn == false))
                        <button type="button" class="btn-primary rounded f-15 ml-4" id="clock-in"
                            @if (($cannotLogin || !is_null($msg)) && ($user->isAdmin($user->id) || $user->isEmployee($user->id)))
                                disabled
                                data-toggle="tooltip" data-placement="top" title="{{__($msg)}}"
                            @endif
                        ><i
                        class="icons icon-login mr-2"></i>@lang('modules.attendance.clock_in')</button>
                    @endif
                @endif

                @if (!is_null($currentClockIn) && is_null($currentClockIn->clock_out_time) || (!is_null($currentClockIn) && $flagbtn == true))
                    <button type="button" class="btn-danger rounded f-15 ml-4" id="clock-out"><i
                            class="icons icon-login mr-2"></i>@lang('modules.attendance.clock_out')</button>
                @endif

                @if (in_array('admin', user_roles()))
                    <div class="private-dash-settings d-flex align-self-center">
                        <x-form id="privateDashboardWidgetForm" method="POST">
                            <div class="dropdown keep-open">
                                <a class="d-flex align-items-center justify-content-center dropdown-toggle px-4 text-dark left-3"
                                    type="link" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"

                                    aria-expanded="false">
                                    <i class="fa fa-cog" data-original-title="{{__('modules.dashboard.dashboardWidgetsSettings')}}" data-toggle="tooltip"></i>
                                </a>
                                <!-- Dropdown - User Information -->
                                <ul class="dropdown-menu dropdown-menu-right dashboard-settings p-20"
                                    aria-labelledby="dropdownMenuLink" tabindex="0">
                                    <li class="border-bottom mb-3">
                                        <h4 class="heading-h3">@lang('modules.dashboard.dashboardWidgets')</h4>
                                    </li>
                                    @foreach ($widgets as $widget)
                                        @php
                                            $wname = \Illuminate\Support\Str::camel($widget->widget_name);
                                        @endphp
                                        <li class="mb-2 float-left w-50">
                                            <div class="checkbox checkbox-info ">
                                                <input id="{{ $widget->widget_name }}" name="{{ $widget->widget_name }}"
                                                    value="true" @if ($widget->status) checked @endif type="checkbox">
                                                <label for="{{ $widget->widget_name }}">@lang('modules.dashboard.' .
                                                    $wname)</label>
                                            </div>
                                        </li>
                                    @endforeach
                                    @if (count($widgets) % 2 != 0)
                                        <li class="mb-2 float-left w-50 height-35"></li>
                                    @endif
                                    <li class="float-none w-100">
                                        <x-forms.button-primary id="save-dashboard-widget" icon="check">@lang('app.save')
                                        </x-forms.button-primary>
                                    </li>
                                </ul>
                            </div>
                        </x-form>
                    </div>
                @endif
            </div>
            <!-- CLOCK IN CLOCK OUT END -->
        </div>
        <!-- WELOCOME END -->
         <!-- EMPLOYEE DASHBOARD DETAIL START -->
         <div class="row emp-dash-detail">
            <!-- EMP DASHBOARD INFO NOTICES START -->
            @if(count(array_intersect(['profile', 'shift_schedule', 'birthday', 'notices'], $activeWidgets)) > 0)
                <div class="col-xl-5 col-lg-12 col-md-12 e-d-info-notices">
                    <div class="row">
                        @if (in_array('profile', $activeWidgets))
                        <!-- EMP DASHBOARD INFO START -->
                        <div class="col-md-12">
                            <div class="card border-0 b-shadow-4 mb-3 e-d-info">
                                <a @if(!in_array('client', user_roles())) href="{{ route('employees.show', user()->id) }}" @endif >
                                    <div class="card-horizontal align-items-center">
                                        <div class="card-img">
                                            <img class="" src=" {{ $user->image_url }}" alt="Card image">
                                        </div>
                                        <div class="card-body border-0 pl-0">
                                            <h4 class="card-title text-dark f-18 f-w-500 mb-0">{{ $user->name }}</h4>
                                            <p class="f-14 font-weight-normal text-dark-grey mb-2">
                                                {{ $user->employeeDetail->designation->name ?? '--' }}</p>
                                            <p class="card-text f-12 text-lightest"> @lang('app.employeeId') :
                                                {{ $user->employeeDetail->employee_id }}</p>
                                        </div>
                                    </div>
                                </a>

                                <div class="card-footer bg-white border-top-grey py-3">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        @if(in_array('tasks', user_modules()))
                                            <span>
                                                <label class="f-12 text-dark-grey mb-12 " for="usr">
                                                    @lang('app.openTasks') </label>
                                                <p class="mb-0 f-18 f-w-500">
                                                    <a href="{{ route('tasks.index') . '?assignee=me' }}"
                                                        class="text-dark">
                                                        {{ $inProcessTasks }}
                                                    </a>
                                                </p>
                                            </span>
                                        @endif
                                        @if(in_array('projects', user_modules()))
                                            <span>
                                                <label class="f-12 text-dark-grey mb-12 " for="usr">
                                                    @lang('app.menu.projects') </label>
                                                <p class="mb-0 f-18 f-w-500">
                                                    <a href="{{ route('projects.index') . '?assignee=me&status=all' }}"
                                                        class="text-dark">{{ $totalProjects }}</a>
                                                </p>
                                            </span>
                                        @endif
                                        @if (isset($totalOpenTickets) && in_array('tickets', user_modules()))
                                            <span>
                                                <label class="f-12 text-dark-grey mb-12 " for="usr">
                                                    @lang('modules.dashboard.totalOpenTickets') </label>
                                                <p class="mb-0 f-18 f-w-500">
                                                    <a href="{{ route('tickets.index') . '?agent=me&status=open' }}"
                                                        class="text-dark">{{ $totalOpenTickets }}</a>
                                                </p>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- EMP DASHBOARD INFO END -->
                        @endif

                        @if (!is_null($myActiveTimer) && in_array('tasks', user_modules()))
                            <div class="col-sm-12" id="myActiveTimerSection">
                                <x-cards.data class="mb-3" :title="__('modules.timeLogs.myActiveTimer')">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            {{ $myActiveTimer->start_time->timezone(company()->timezone)->translatedFormat('M d, Y' . ' - ' . company()->time_format) }}
                                            <p class="text-primary my-2">

                                                <strong>@lang('modules.timeLogs.totalHours'):</strong>
                                                {{ \Carbon\CarbonInterval::formatHuman(now()->diffInMinutes($myActiveTimer->start_time) - $myActiveTimer->breaks->sum('total_minutes')) }}
                                            </p>

                                            <ul class="list-group">
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                                                    <span><i class="fa fa-clock"></i>
                                                        @lang('modules.timeLogs.startTime')</span>
                                                    {{ $myActiveTimer->start_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                                                    <span><i class="fa fa-briefcase"></i> @lang('app.task')</span>
                                                    <a href="{{ route('tasks.show', $myActiveTimer->task->id) }}"
                                                        class="text-dark-grey openRightModal">{{ $myActiveTimer->task->heading }}</a>
                                                </li>
                                                @foreach ($myActiveTimer->breaks as $item)
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                                                        @if (!is_null($item->end_time))

                                                            <span><i class="fa fa-mug-hot"></i>
                                                                @lang('modules.timeLogs.break')
                                                                ({{ \Carbon\CarbonInterval::formatHuman($item->end_time->diffInMinutes($item->start_time)) }})
                                                            </span>
                                                            {{ $item->start_time->timezone(company()->timezone)->translatedFormat(company()->time_format) . ' - ' . $item->end_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                                        @else
                                                            <span><i class="fa fa-mug-hot"></i>
                                                                @lang('modules.timeLogs.break')</span>
                                                            {{ $item->start_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>

                                        </div>
                                        <div class="col-sm-12 pt-3 text-right">
                                            @if ($editTimelogPermission == 'all' || ($editTimelogPermission == 'added' && $myActiveTimer->added_by == user()->id) || ($editTimelogPermission == 'owned' && (($myActiveTimer->project && $myActiveTimer->project->client_id == user()->id) || $myActiveTimer->user_id == user()->id)) || ($editTimelogPermission == 'both' && (($myActiveTimer->project && $myActiveTimer->project->client_id == user()->id) || $myActiveTimer->user_id == user()->id || $myActiveTimer->added_by == user()->id)))
                                                @if (is_null($myActiveTimer->activeBreak))
                                                    <x-forms.button-secondary icon="pause-circle"
                                                        data-time-id="{{ $myActiveTimer->id }}" id="pause-timer-btn" data-url="{{ url()->current() }}">
                                                        @lang('modules.timeLogs.pauseTimer')</x-forms.button-secondary>
                                                    <x-forms.button-primary class="ml-3 stop-active-timer" data-url="{{ url()->current() }}"
                                                        data-time-id="{{ $myActiveTimer->id }}" icon="stop-circle">
                                                        @lang('modules.timeLogs.stopTimer')</x-forms.button-primary>
                                                @else
                                                    <x-forms.button-primary id="resume-timer-btn" icon="play-circle" data-url="{{ url()->current() }}"
                                                        data-time-id="{{ $myActiveTimer->activeBreak->id }}">
                                                        @lang('modules.timeLogs.resumeTimer')</x-forms.button-primary>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </x-cards.data>
                            </div>
                        @endif

                            @include('dashboard.employee.widgets.shift_schedule')

                            @include('dashboard.employee.widgets.birthday')

                            @include('dashboard.employee.widgets.appreciation')

                            @include('dashboard.employee.widgets.leave')

                            @include('dashboard.employee.widgets.work_from_home')

                            @include('dashboard.employee.widgets.work_anniversary')

                            @include('dashboard.employee.widgets.notice-period')

                            @include('dashboard.employee.widgets.probation')

                            @include('dashboard.employee.widgets.internship')

                            @include('dashboard.employee.widgets.contract')
                    </div>
                </div>
            @endif
            <!-- EMP DASHBOARD INFO NOTICES END -->
            <!-- EMP DASHBOARD TASKS PROJECTS EVENTS START -->
            <div class="col-xl-7 col-lg-12 col-md-12 e-d-tasks-projects-events">
                <!-- EMP DASHBOARD TASKS PROJECTS START -->
                <div class="row mb-3 mt-xl-0 mt-lg-4 mt-md-4 mt-4">
                    @if (in_array('tasks', $activeWidgets) && (!is_null($viewTaskPermission) && $viewTaskPermission != 'none') && in_array('tasks', user_modules()))
                        <div class="col-md-6 mb-3">
                            <div
                                class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
                                <div class="d-block ">
                                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">@lang('app.menu.tasks')</h5>
                                    <div class="d-flex">
                                        <a href="{{ route('tasks.index') . '?assignee=me' }}">
                                            <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
                                                {{ $inProcessTasks }}<span class="f-12 font-weight-normal text-lightest">
                                                    @lang('app.pending') </span>
                                            </p>
                                        </a>
                                        <a href="{{ route('tasks.index') . '?assignee=me&overdue=yes' }}">
                                            <p class="mb-0 f-21 font-weight-bold text-red d-grid">{{ $dueTasks }}<span
                                                    class="f-12 font-weight-normal text-lightest">@lang('app.overdue')</span>
                                            </p>
                                        </a>
                                    </div>
                                </div>
                                <div class="d-block">
                                    <i class="fa fa-list text-lightest f-27"></i>
                                </div>
                            </div>
                        </div>
                    @endif

                    @include('dashboard.employee.widgets.projects')
                    @include('dashboard.employee.widgets.follow_ups')
                    @include('dashboard.employee.widgets.lead')
                    @include('dashboard.employee.widgets.week_timelog')
                </div>
                <!-- EMP DASHBOARD TASKS PROJECTS END -->
                @include('dashboard.employee.widgets.my_tasks')
                @include('dashboard.employee.widgets.tickets')
                @include('dashboard.employee.widgets.my_calendar')
                @include('dashboard.employee.widgets.notices')

            </div>
            <!-- EMP DASHBOARD TASKS PROJECTS EVENTS END -->
        </div>
        <!-- EMPLOYEE DASHBOARD DETAIL END -->

    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @if ((!is_null($viewEventPermission) && $viewEventPermission != 'none')
        || (!is_null($viewHolidayPermission) && $viewHolidayPermission != 'none')
        || (!is_null($viewTaskPermission) && $viewTaskPermission != 'none')
        || (!is_null($viewTicketsPermission) && $viewTicketsPermission != 'none')
        || (!is_null($viewLeavePermission) && $viewLeavePermission != 'none')
        )
        <script src="{{ asset('vendor/full-calendar/main.min.js') }}"  defer="defer"></script>
        <script src="{{ asset('vendor/full-calendar/locales-all.min.js') }}"  defer="defer"></script>
        <script>

            var clockInButton = document.getElementById('clock-in');

            if (clockInButton) {

                var cannotLogin = "{{ $cannotLogin }}";
                var checkTodayHoliday = "{{ $checkTodayHoliday?->exists }}";
                var leaveApplied = "{{ $leave->where('user_id', auth()->user()->id)->where('status', 'approved')->first()?->exists }}";
                var shiftAssigned = "{{ $shiftAssigned?->exists}}";
                var allowOutsideShift = "{{ attendance_setting()->show_clock_in_button }}";
                var isDefaultShiftAssignedToday = "{{ $isDefaultShiftAssignedToday }}";

                var currentDateTime = "{{ $currentDateTime }}";
                var startTime = "{{ $start_time }}";
                var midTime = "{{ $mid_time }}";
                var endTime = "{{ $end_time }}";

                if (
                    cannotLogin === 'false' ||
                    (cannotLogin === '' && (checkTodayHoliday == false && leaveApplied == false))
                ) {

                    if(!shiftAssigned && !isDefaultShiftAssignedToday){
                        clockInButton.disabled = true;
                        $(clockInButton).tooltip('dispose');
                    }else{
                        clockInButton.disabled = false;
                        $(clockInButton).tooltip();
                    }
                } else if(leaveApplied == true){

                    var leaveDuration = "{{ $leaveApplied?->duration }}";
                    var halfDayType = "{{ $leaveApplied?->half_day_type }}";

                    if(
                        ((leaveDuration === 'single' || leaveDuration === 'multiple') && currentDateTime >= startTime && currentDateTime <= endTime) ||
                        (leaveDuration === 'half day' && halfDayType === 'first_half' && currentDateTime < midTime && currentDateTime >= startTime && currentDateTime <= midTime) ||
                        (leaveDuration === 'half day' && halfDayType === 'second_half' && currentDateTime > midTime && currentDateTime >= midTime && currentDateTime <= endTime)
                    ){

                        clockInButton.disabled = true;
                        $(clockInButton).tooltip('dispose');
                    }
                }else if(checkTodayHoliday == true && currentDateTime >= startTime && currentDateTime <= endTime){

                    if(shiftAssigned == true){
                        clockInButton.disabled = false;
                        $(clockInButton).tooltip();
                    }else{
                        clockInButton.disabled = true;
                        $(clockInButton).tooltip('dispose');
                    }

                }else if(checkTodayHoliday == true && (currentDateTime <= startTime || currentDateTime >= endTime)){

                    if(shiftAssigned == true){

                        if(allowOutsideShift == 'yes'){

                            clockInButton.disabled = false;
                            $(clockInButton).tooltip();
                        }
                    }else{

                        clockInButton.disabled = true;
                        $(clockInButton).tooltip('dispose');
                    }

                }else {

                    clockInButton.disabled = true;
                    $(clockInButton).tooltip('dispose');
                }
            }
        </script>
        <script>

            $(document).ready(function () {
                var calendarEl = document.getElementById('calendar');

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: initialLocaleCode,
                    timeZone: '{{ company()->timezone }}',
                    firstDay: parseInt("{{ attendance_setting()?->week_start_from }}"),
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },
                    navLinks: true, // can click day/week names to navigate views
                    selectable: false,
                    initialView: 'listWeek',
                    selectMirror: true,
                    select: function(arg) {
                        addEventModal(arg.start, arg.end, arg.allDay);
                        calendar.unselect()
                    },
                    eventClick: function(arg) {
                        getEventDetail(arg.event.id,arg.event.extendedProps.event_type);
                    },
                    editable: false,
                    dayMaxEvents: true, // allow "more" link when too many events
                    events: {
                        url: "{{ route('dashboard.private_calendar') }}",
                    },
                    eventDidMount: function(info) {
                            $(info.el).css('background-color', info.event.extendedProps.bg_color);
                            $(info.el).css('color', info.event.extendedProps.color);
                            $(info.el).find('td.fc-list-event-title').prepend('<i class="fa '+info.event.extendedProps.icon+'"></i>&nbsp;&nbsp;');
                            // tooltip for leaves
                            if(info.event.extendedProps.event_type == 'leave'){
                                $(info.el).find('td.fc-list-event-title > a').css('cursor','default'); // list view cursor for leave
                                $(info.el).css('cursor','default')
                                $(info.el).tooltip({
                                    title: info.event.extendedProps.name,
                                    container: 'body',
                                    delay: { "show": 50, "hide": 50 }
                                });
                        }
                    },
                    eventTimeFormat: { // like '14:30:00'
                        hour: company.time_format == 'H:i' ? '2-digit' : 'numeric',
                        minute: '2-digit',
                        meridiem: company.time_format == 'H:i' ? false : true
                    }
                });

                if (calendarEl != null) {
                    calendar.render();
                }


                $('.cal-filter').on('click', function() {

                    var filter = [];

                    $('.filter-check:checked').each(function() {
                        filter.push($(this).val());
                    });

                    if(filter.length < 1){
                        filter.push('None');
                    }

                    calendar.removeAllEventSources();
                    calendar.addEventSource({
                        url: "{{ route('dashboard.private_calendar') }}",
                        extraParams: {
                            filter: filter
                        }
                    });

                    filter = null;
                });

            })
        </script>
        <script>
            var initialLocaleCode = '{{ user()->locale }}';

            // Task Detail show in sidebar
            var getEventDetail = function(id,type) {
                if(type == 'ticket')
                {
                    var url = "{{ route('tickets.show', ':id') }}";
                        url = url.replace(':id', id);
                        window.location = url;
                        return true;
                }

                if(type == 'leave')
                {
                    return true;
                }

                openTaskDetail();

                switch (type) {
                    case 'task':
                        var url = "{{ route('tasks.show', ':id') }}";
                        break;
                    case 'event':
                        var url = "{{ route('events.show', ':id') }}";
                        break;
                    case 'holiday':
                        var url = "{{ route('holidays.show', ':id') }}";
                        break;
                    case 'leave':
                        var url = "{{ route('leaves.show', ':id') }}";
                        break;
                    default:
                        return 0;
                        break;
                }

                url = url.replace(':id', id);

                $.easyAjax({
                    url: url,
                    blockUI: true,
                    container: RIGHT_MODAL,
                    historyPush: true,
                    success: function(response) {
                        if (response.status == "success") {
                            $(RIGHT_MODAL_CONTENT).html(response.html);
                            $(RIGHT_MODAL_TITLE).html(response.title);
                        }
                    },
                    error: function(request, status, error) {
                        if (request.status == 403) {
                            $(RIGHT_MODAL_CONTENT).html(
                                '<div class="align-content-between d-flex justify-content-center mt-105 f-21">403 | Permission Denied</div>'
                            );
                        } else if (request.status == 404) {
                            $(RIGHT_MODAL_CONTENT).html(
                                '<div class="align-content-between d-flex justify-content-center mt-105 f-21">404 | Not Found</div>'
                            );
                        } else if (request.status == 500) {
                            $(RIGHT_MODAL_CONTENT).html(
                                '<div class="align-content-between d-flex justify-content-center mt-105 f-21">500 | Something Went Wrong</div>'
                            );
                        }
                    }
                });

            };

            // calendar filter
            var hideDropdown = false;

            $('#event-btn').click(function(){
                if(hideDropdown == true)
                {
                    $('#cal-drop').hide();
                    hideDropdown = false;
                }
                else
                {
                    $('#cal-drop').toggle();
                    hideDropdown = true;
                }
            });


            $(document).mouseup(e => {

                const $menu = $('.calendar-action');

                if (!$menu.is(e.target) && $menu.has(e.target).length === 0)
                {
                    hideDropdown = false;
                    $('#cal-drop').hide();
                }
            });

        </script>
    @endif

    <script>
        window.setInterval(function () {
            let date = new Date();
            $('#dashboard-clock').html(moment.tz(date, "{{ company()->timezone }}").format(MOMENTJS_TIME_FORMAT))
        }, 1000);

        $('#save-dashboard-widget').click(function() {
            $.easyAjax({
                url: "{{ route('dashboard.widget', 'private-dashboard') }}",
                container: '#privateDashboardWidgetForm',
                blockUI: true,
                type: "POST",
                redirect: true,
                data: $('#privateDashboardWidgetForm').serialize(),
                success: function() {
                    window.location.reload();
                }
            })
        });

        $('#clock-in').click(function() {
            const url = "{{ route('attendances.clock_in_modal') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('.request-shift-change').click(function() {
            var id = $(this).data('shift-schedule-id');
            var date = $(this).data('shift-schedule-date');
            var shiftId = $(this).data('shift-id');
            var url = "{{ route('shifts-change.edit', ':id') }}?date="+date+"&shift_id="+shiftId;
            url = url.replace(':id', id);

            $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_DEFAULT, url);
        });

        $('#view-shifts').click(function() {
            const url = "{{ route('employee-shifts.index') }}";
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        @if (!is_null($currentClockIn))
            $('#clock-out').click(function() {
                var url = "{{ route('attendances.show_clocked_hours') }}?aid={{ $currentClockIn->id }}";

                $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);

            });

            function clockOut()
            {

                var token = "{{ csrf_token() }}";
                var currentLatitude = document.getElementById("current-latitude").value;
                var currentLongitude = document.getElementById("current-longitude").value;

                $.easyAjax({
                    url: "{{ route('attendances.update_clock_in') }}",
                    type: "GET",
                    data: {
                        currentLatitude: currentLatitude,
                        currentLongitude: currentLongitude,
                        _token: token,
                        id: '{{ $currentClockIn->id }}'
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location.reload();
                        }
                    }
                });
            }
        @endif

        $('.keep-open .dropdown-menu').on({
            "click": function(e) {
                e.stopPropagation();
            }
        });

        $('#weekly-timelogs').on('click', '.week-timelog-day', function() {
            var date = $(this).data('date');

            $.easyAjax({
                url: "{{ route('dashboard.week_timelog') }}",
                container: '#weekly-timelogs',
                blockUI: true,
                type: "POST",
                redirect: true,
                data: {
                    'date': date,
                    '_token': "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#weekly-timelogs').html(response.html)
                }
            })
        });


        @if (session('success'))
            Swal.fire({
                icon: 'success',
                text: '{{ session('success') }}',

                toast: true,
                position: "top-end",
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,

                customClass: {
                    confirmButton: "btn btn-primary",
                },
                showClass: {
                    popup: "swal2-noanimation",
                    backdrop: "swal2-noanimation",
                },
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                text: '{{ session('error') }}',

                toast: true,
                position: "top-end",
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,

                customClass: {
                    confirmButton: "btn btn-primary",
                },
                showClass: {
                    popup: "swal2-noanimation",
                    backdrop: "swal2-noanimation",
                },
            });
        @endif
    </script>

    @if (attendance_setting()->radius_check == 'yes' || attendance_setting()->save_current_location)
    <script>
        function setCurrentLocation() {
            const currentLatitude = document.getElementById("current-latitude");
            const currentLongitude = document.getElementById("current-longitude");

            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition);
                }
            }

            function showPosition(position) {
                currentLatitude.value = position.coords.latitude;
                currentLongitude.value = position.coords.longitude;
            }
            getLocation();

        }

        setCurrentLocation();
    </script>

    @endif
@endpush
