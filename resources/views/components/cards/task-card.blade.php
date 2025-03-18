@php
$moveClass = '';
@endphp
@if ($draggable == 'false')
    @php
        $moveClass = 'move-disable';
    @endphp
@endif

<style>
    .projectNameSpan {
        white-space: normal
    }
</style>

@php
    $priorityColor = ($task->priority == 'high' ? '#dd0000' : ($task->priority == 'medium' ? '#ffc202' : '#0a8a1f'));
@endphp
<div class="card rounded bg-white border-grey b-shadow-4 m-1 mb-2 {{ $moveClass }} task-card"
    data-task-id="{{ $task->id }}" data-need-approval="{{ optional($task->project)->need_approval_by_admin ?? 0 }}" id="drag-task-{{ $task->id }}" style="border-left: 3px solid {{ $priorityColor }}; background-color: {{ $priorityColor.'08 !important;' }}">
    <div class="card-body p-2">
        <div class="d-flex justify-content-between mb-1">
            <a href="{{ route('tasks.show', [$task->id]) }}"
                class="f-12 f-w-500 text-dark mb-0 text-wrap openRightModal">{{ $task->heading }}</a>
            <p class="f-12 font-weight-bold text-dark-grey mb-0">
                @if ($task->is_private)
                    <span class='badge badge-secondary mr-1'><i class='fa fa-lock'></i>
                        @lang('app.private')</span>
                @endif
                #{{ $task->task_short_code }}
            </p>
        </div>

        @if (count($task->labels) > 0)
            <div class="mb-1 d-flex flex-wrap">
                @foreach ($task->labels as $key => $label)
                    <span class='badge badge-secondary mr-1'
                        style="background:{{ $label->label_color }}">{{ $label->label_name }}
                    </span>
                @endforeach
            </div>
        @endif

        <div class="d-flex mb-1 justify-content-between">
            @if ($task->project_id)
                <div>
                    <i class="fa fa-layer-group f-11 text-lightest"></i><span
                        class="ml-2 f-11 text-lightest projectNameSpan">{{ $task->project->project_name }}</span>
                </div>
            @endif

            @if ($task->estimate_hours > 0 || $task->estimate_minutes > 0)
                <div  data-toggle="tooltip" data-original-title="@lang('app.estimate'): {{ $task->estimate_hours }} @lang('app.hrs') {{ $task->estimate_minutes }} @lang('app.mins')">
                    <i class="fa fa-hourglass-half f-11 text-lightest"></i><span
                        class="ml-2 f-11 text-lightest">{{ $task->estimate_hours }}:{{ $task->estimate_minutes }}</span>
                </div>
            @endif
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-wrap">
                @foreach ($task->users as $item)
                    <div class="avatar-img mr-1 rounded-circle">
                        <a href="{{ route('employees.show', $item->id) }}" alt="{{ $item->name }}"
                            data-toggle="tooltip" data-original-title="{{ $item->name }}"
                            data-placement="right"><img src="{{ $item->image_url }}"></a>
                    </div>
                @endforeach
            </div>
            @if ($task->subtasks_count > 0)
                {{ $task->completed_subtasks_count .'/' . $task->subtasks_count }}
            @endif
            @if (!is_null($task->due_date))
                @if ($task->due_date->endOfDay()->isPast())
                    <div class="d-flex text-red">
                        <span class="f-12 ml-1"><i class="f-11 bi bi-calendar align-self-center"></i> {{ $task->due_date->translatedFormat($company->date_format) }}</span>
                    </div>
                @elseif($task->due_date->setTimezone($company->timezone)->isToday())
                    <div class="d-flex text-dark-green">
                        <i class="fa fa-calendar-alt f-11 align-self-center"></i><span class="f-12 ml-1">@lang('app.today')</span>
                    </div>
                @else
                    <div class="d-flex text-lightest">
                        <i class="fa fa-calendar-alt f-11 align-self-center"></i><span
                            class="f-12 ml-1">{{ $task->due_date->translatedFormat($company->date_format) }}</span>
                    </div>
                @endif
            @endif

        </div>
    </div>
</div><!-- div end -->
