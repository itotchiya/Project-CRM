@php
$editTimelogPermission = user()->permission('edit_timelogs');
$addTaskPermission = user()->permission('add_tasks');
@endphp
<style>
    #employee-header{
        padding-left: 58px !important;
    }
    .project-name{
        width:143px !important;
    }
    .employee-user{
        padding-right: 38px !important;

    }
    </style>
@include('sections.datatable_css')
<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.projects.activeTimers')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body py-0">
    <div class="row">
        @if (!is_null($myActiveTimer))
            <div class="col-lg-4 col-md-5 bg-additional-grey py-3" id="myActiveTimer">
                <h4 class="heading-h4">@lang('modules.timeLogs.myActiveTimer')</h4>
                <x-cards.data>
                    <div class="row">
                        <div class="col-sm-12">
                            {{$myActiveTimer->start_time->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                            <p class="text-primary my-2">
                                @php
                                    $totalMinutes =  now()->diffInMinutes($myActiveTimer->start_time) - $myActiveTimer->breaks->sum('total_minutes');
                                @endphp

                                <strong>@lang('modules.timeLogs.totalHours'):</strong>
                                {{\Carbon\CarbonInterval::formatHuman($totalMinutes)}}
                            </p>

                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                                    <span><i class="fa fa-clock"></i> @lang('modules.timeLogs.startTime')</span>
                                    {{ $myActiveTimer->start_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                                    <span><i class="fa fa-briefcase"></i> @lang('app.task')</span>
                                    {{ $myActiveTimer->task->heading }}
                                </li>
                                @foreach ($myActiveTimer->breaks as $item)
                                    <li class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                                        @if (!is_null($item->end_time))

                                            <span><i class="fa fa-mug-hot"></i> @lang('modules.timeLogs.break')
                                                {{\Carbon\CarbonInterval::formatHuman($item->end_time->diffInMinutes($item->start_time))}}
                                            </span>
                                            {{ $item->start_time->timezone(company()->timezone)->translatedFormat(company()->time_format) . ' - ' . $item->end_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}

                                        @else
                                            <span><i class="fa fa-mug-hot"></i> @lang('modules.timeLogs.break')</span>
                                            {{ $item->start_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                        </div>
                        <div class="col-sm-12 pt-3 text-right">
                            @if (
                                    $editTimelogPermission == 'all'
                                    || ($editTimelogPermission == 'added' && $myActiveTimer->added_by == user()->id)
                                    || ($editTimelogPermission == 'owned'
                                        && (($myActiveTimer->project && $myActiveTimer->project->client_id == user()->id) || $myActiveTimer->user_id == user()->id)
                                        )
                                    || ($editTimelogPermission == 'both' && (($myActiveTimer->project && $myActiveTimer->project->client_id == user()->id) || $myActiveTimer->user_id == user()->id || $myActiveTimer->added_by == user()->id))
                                )

                                @if (is_null($myActiveTimer->activeBreak))
                                    <x-forms.button-secondary icon="pause-circle" data-time-id="{{ $myActiveTimer->id }}" id="pause-timer-btn" data-url="{{ url()->current() }}">@lang('modules.timeLogs.pauseTimer')</x-forms.button-secondary>
                                @else
                                    <x-forms.button-secondary id="resume-timer-btn" icon="play-circle" data-url="{{ url()->current() }}"
                                    data-time-id="{{ $myActiveTimer->activeBreak->id }}">@lang('modules.timeLogs.resumeTimer')</x-forms.button-secondary>
                                @endif
                                <x-forms.button-primary class="ml-3 stop-active-timer" data-url="{{ url()->current() }}" data-time-id="{{ $myActiveTimer->id }}" icon="stop-circle">@lang('modules.timeLogs.stopTimer')</x-forms.button-primary>
                            @endif
                        </div>
                    </div>
                </x-cards.data>
            </div>
        @else
            <div class="col-lg-4 bg-additional-grey py-3">
                <x-cards.data :title="__('modules.timeLogs.startTimer')">
                    <x-form id="startTimerForm">
                        <input type="hidden" name="user_id[]" value="{{ user()->id }}">
                        <div class="row">
                            <div class="col">
                                <x-forms.select fieldId="project_id" fieldName="project_id" :fieldLabel="__('app.project')"
                                                search="true">
                                    <option value="">--</option>
                                    @foreach ($projects as $data)
                                        <option value="{{ $data->id }}">
                                            {{ $data->project_name }}
                                        </option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col" id="task_div">
                                <x-task-selection-dropdown :tasks="$tasks" />
                            </div>
                        </div>

                        <div class="row">
                            @if ($addTaskPermission == 'all' || $addTaskPermission == 'added')

                                <div class="col">
                                    <div class="form-group">
                                        <div class="d-flex mt-3">
                                            <x-forms.checkbox :fieldLabel="__('app.create') . ' ' . __('modules.tasks.newTask')"
                                                fieldName="create_task" fieldId="create_task" />
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <x-forms.text fieldId="memo" fieldName="memo" :fieldLabel="__('modules.timeLogs.memo')"
                                    fieldRequired="true" />
                            </div>
                            <div class="col-12 text-right">
                                <x-forms.button-primary id="start-timer-btn" icon="play">@lang('modules.timeLogs.startTimer')</x-forms.button-primary>
                            </div>
                        </div>

                    </x-form>
                </x-cards.data>
            </div>
        @endif

        <div class="my-3 col-lg-8 col-md-7">
            <div class="table-responsive">
                <x-table class="table-bordered table-hover rounded" id="active-timer-table" width="100%" headType="thead-light">
                    <x-slot name="thead">
                        <th>#</th>
                        <th>@lang('app.task')</th>
                        <th id="employee-header" width="5%" style="width: 20px !important;">@lang('app.employee')</th>
                        <th class="text-right w-180">@lang('modules.timeLogs.startTime')</th>
                        <th class="text-right w-150">@lang('app.action')</th>
                    </x-slot>

                    @forelse ($activeTimers as $key => $item)
                        @if (is_null($item->activeBreak))

                        <tr id="timer-{{ $item->id }}">
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <a href="{{ route('tasks.show', $item->task_id) }}" class="text-darkest-grey">
                                    {{ $item->task->heading }}
                                </a>
                                @if ($item->task->project_id)
                                    <p class="text-lightest mb-0 project-name">{{ $item->task->project->project_name }}</p>
                                @endif
                            </td>
                            <td class="text-right employee-user"  width="5%">
                               <div style="display: none"> {{ $item->user->name }} </div>
                                <x-employee-image :user="$item->user" />
                            </td>
                            <td class="text-right">
                                {{ $item->start_time->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                <div class="mt-1 f-12">
                                    @if (is_null($item->activeBreak))
                                        <span class="badge badge-secondary">
                                            <i data-toggle="tooltip" data-original-title="@lang('app.active')"
                                            class="fa fa-hourglass-start"></i>
                                            {{\Carbon\CarbonInterval::formatHuman(now()->diffInMinutes($item->start_time) - $item->breaks->sum('total_minutes'))}}
                                        </span>
                                    @else
                                        <span class="badge badge-primary" data-toggle="tooltip" data-original-title="{{ $item->activeBreak->start_time->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}">
                                            <i class="fa fa-pause-circle"></i> @lang('modules.timeLogs.paused')
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right">
                                @if (
                                    $editTimelogPermission == 'all'
                                    || ($editTimelogPermission == 'added' && $item->added_by == user()->id)
                                    || ($editTimelogPermission == 'owned'
                                        && (($item->project && $item->project->client_id == user()->id) || $item->user_id == user()->id)
                                        )
                                    || ($editTimelogPermission == 'both' && (($item->project && $item->project->client_id == user()->id) || $item->user_id == user()->id || $item->added_by == user()->id))
                                )
                                <x-forms.button-secondary class="stop-active-timer" icon="stop-circle"
                                    data-time-id="{{ $item->id }}">@lang('app.stop')</x-forms.button-secondary>
                                @endif
                            </td>
                        </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="5">
                                <x-cards.no-record icon="clock" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse

                </x-table>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
</div>

<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>

    $(function(){

        $(document).ready(function () {

            $('#active-timer-table').DataTable({
                dom: "<'row'<'col-sm-12'tr>><'d-flex'<'flex-grow-1'l><i><p>>",
                pageLength:{{companyOrGlobalSetting()->datatable_row_limit ?? 10}},
            });
        });

        $('#start-timer-btn').click(function() {
            var url = "{{ route('timelogs.start_timer') }}";
            $.easyAjax({
                url: url,
                container: '#startTimerForm',
                type: "POST",
                blockUI: true,
                disableButton: true,
                buttonSelector: "#start-timer-btn",
                data: $('#startTimerForm').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if (response.activeTimerCount > 0) {
                            $('#show-active-timer .active-timer-count').html(response.activeTimerCount);
                            $('#show-active-timer .active-timer-count').removeClass('d-none');
                        } else {
                            $('#show-active-timer .active-timer-count').addClass('d-none');
                        }

                        $('#timer-clock').html(response.clockHtml);

                        $(MODAL_XL).modal('hide');
                    }
                }
            })
        });

        $("input[name=create_task]").click(function() {
            $('#task_div').toggleClass('d-none');
        });

    });

    $('#startTimerForm').on('change', '#project_id', function () {
        let id = $(this).val();
        if (id === '') {
            id = 0;
        }
        let url = "{{ route('projects.pendingTasks', ':id') }}";
        url = url.replace(':id', id);

        $.easyAjax({
            url: url,
            container: '#startTimerForm',
            type: "GET",
            blockUI: true,
            success: function (response) {
                if (response.status == 'success') {
                    $('#timer_task_id').html(response.data);
                    $('#timer_task_id').selectpicker('refresh');
                }
            }
        });
    });

    init(MODAL_XL);

@if(!is_null($myActiveTimer) && Route::current()->getName() != "timelogs.start_timer")
    $('.stop-active-timer').click(function(){
        var url = "{{ route('timelogs.stopper_alert', ':id') }}?via=timelog";
        var id = "{{$selfActiveTimer->id}}"
        url = url.replace(':id', id);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })
@endif

</script>
