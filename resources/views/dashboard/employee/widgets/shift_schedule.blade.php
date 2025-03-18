@if (in_array('attendance', user_modules()) && in_array('shift_schedule', $activeWidgets) && $sidebarUserPermissions['view_shift_roster'] != 5 && $sidebarUserPermissions['view_shift_roster'] != 'none' && in_array('attendance', user_modules()))
    <div class="col-sm-12">
        <x-cards.data class="mb-3" :title="__('modules.attendance.shiftSchedule')" padding="false" otherClasses="h-200">
            <x-slot name="action">
                <x-forms.button-primary id="view-shifts">@lang('modules.attendance.shift')
                </x-forms.button-primary>
            </x-slot>

            <x-table>
                @foreach ($currentWeekDates as $key => $weekDate)
                    @if (isset($weekShifts[$key]))
                        <tr>
                            <td class="pl-20">
                                {{ $weekDate->translatedFormat(company()->date_format) }}
                            </td>
                            <td>{{ $weekDate->translatedFormat('l') }}</td>
                            <td>
                                @if (isset($weekShifts[$key]->shift))
                                    @if ($weekShifts[$key]->shift->shift_name == 'Day Off')
                                        <span class="badge badge-secondary text-body"
                                              style="background-color:{{ $weekShifts[$key]->shift->color }}">{{ __('modules.attendance.' . str($weekShifts[$key]->shift->shift_name)->camel()) }}
                                                                </span>
                                    @else
                                        <span class="badge badge-success"
                                              style="background-color:{{ $weekShifts[$key]->shift->color }}">{{ $weekShifts[$key]->shift->shift_name }}
                                                                </span>
                                    @endif

                                    @if (!is_null($weekShifts[$key]->remarks) && $weekShifts[$key]->remarks != '')
                                        <i class="fa fa-info-circle text-dark-grey" data-toggle="popover"
                                           data-placement="top" data-content="{{ $weekShifts[$key]->remarks }}"
                                           data-html="true" data-trigger="hover"></i>
                                    @endif
                                @else
                                    {!! $weekShifts[$key] !!}
                                @endif
                            </td>
                            <td class="pr-20 text-right">
                                @if (isset($weekShifts[$key]->shift))
                                    @if (attendance_setting()->allow_shift_change && !$weekDate->isPast())
                                        @if (!is_null($weekShifts[$key]->requestChange) && $weekShifts[$key]->requestChange->status == 'waiting')
                                            <div class="task_view">
                                                <a href="javascript:;"
                                                   data-shift-schedule-id="{{ $weekShifts[$key]->id }}"
                                                   data-shift-schedule-date="{{ $weekDate->translatedFormat(company()->date_format) }}"
                                                   data-shift-id="{{$weekShifts[$key]->shift->id}}"
                                                   class="taskView border-right-0 request-shift-change f-11">@lang('modules.attendance.requestPending')</a>
                                            </div>
                                        @else
                                            <div class="task_view">
                                                <a href="javascript:;"
                                                   data-shift-schedule-id="{{ $weekShifts[$key]->id }}"
                                                   data-shift-schedule-date="{{ $weekDate->translatedFormat(company()->date_format) }}"
                                                   data-shift-id="{{$weekShifts[$key]->shift->id}}"
                                                   class="taskView border-right-0 request-shift-change f-11">@lang('modules.attendance.requestChange')</a>
                                            </div>
                                        @endif
                                    @else
                                        --
                                    @endif
                                @else
                                    @lang('app.defaultShift')
                                @endif

                            </td>
                        </tr>
                    @endif
                @endforeach
            </x-table>
        </x-cards.data>
    </div>
@endif
