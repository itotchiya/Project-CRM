@if (in_array('my_task', $activeWidgets) && (!is_null($viewTaskPermission) && $viewTaskPermission != 'none' && in_array('tasks', user_modules())))
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 b-shadow-4 mb-3 e-d-info">
                <x-cards.data :title="__('modules.tasks.myTask')" padding="false" otherClasses="h-200">
                    <x-table>
                        <x-slot name="thead">
                            <th>@lang('app.task')#</th>
                            <th>@lang('app.task')</th>
                            <th>@lang('app.status')</th>
                            <th class="text-right pr-20">@lang('app.dueDate')</th>
                        </x-slot>

                        @forelse ($pendingTasks as $task)
                            <tr>
                                <td class="pl-20">
                                    <a
                                        href="{{ route('tasks.show', [$task->id]) }}"
                                        class="openRightModal f-12 mb-1 text-darkest-grey">#{{ $task->task_short_code }}</a>

                                </td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <h5 class="f-12 mb-1 text-darkest-grey"><a
                                                    href="{{ route('tasks.show', [$task->id]) }}"
                                                    class="openRightModal">{{ $task->heading }}</a>
                                            </h5>
                                            <p class="mb-0">
                                                @foreach ($task->labels as $label)
                                                    <span class="badge badge-secondary mr-1"
                                                          style="background-color: {{ $label->label_color }}">{{ $label->label_name }}</span>
                                                @endforeach
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="pr-20">
                                    <i class="fa fa-circle mr-1 text-yellow"
                                       style="color: {{ $task->boardColumn->label_color }}"></i>
                                    {{ $task->boardColumn->column_name }}
                                </td>
                                <td class="pr-20" align="right">
                                    @if (is_null($task->due_date))
                                        --
                                    @elseif ($task->due_date->endOfDay()->isPast())
                                        <span class="text-danger">{{ $task->due_date->translatedFormat(company()->date_format) }}</span>
                                    @elseif ($task->due_date->setTimezone(company()->timezone)->isToday())
                                        <span class="text-success">{{ __('app.today') }}</span>
                                    @else
                                        <span>{{ $task->due_date->translatedFormat(company()->date_format) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="shadow-none">
                                    <x-cards.no-record icon="tasks" :message="__('messages.noRecordFound')"/>
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </x-cards.data>
            </div>
        </div>
    </div>
@endif
