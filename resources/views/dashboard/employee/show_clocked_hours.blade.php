<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.attendanceDetails')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>

<div class="modal-body bg-grey">

    <div class="row">
        @php
            $minimumHalfDayMinutes = ((float)$attendance->shift->flexible_half_day_hours * 60);
            $totalMinimumMinutes = ((float)$attendance->shift->flexible_total_hours * 60);
            $clockedTotalMinutes = floor((float)$totalTime / 60);
        @endphp

        @if ($attendance->shift->shift_type == 'flexible')

            @if ($clockedTotalMinutes < $minimumHalfDayMinutes)
            <div class="col-md-12">
                <x-alert type="warning">@lang('messages.halfdayHoursNotComplete')</x-alert>
            </div>            
            @elseif($clockedTotalMinutes >= $minimumHalfDayMinutes && $clockedTotalMinutes < $totalMinimumMinutes)
            <div class="col-md-12">
                <x-alert type="warning">@lang('messages.willMarkHalfDay')</x-alert>
            </div>  
            @endif
        @endif

        <div class="col-md-6">
            <x-cards.data :title="__('app.date').' - '.$attendanceDate->translatedFormat(company()->date_format) .' ('.$attendanceDate->translatedFormat('l').')'">
                <div class="punch-status">
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="f-13">@lang('modules.attendance.clock_in')</h6>
                        <p class="mb-0">{{ $startTime->translatedFormat(company()->time_format) }}</p>
                    </div>
                    <div class="punch-info">
                        <div class="punch-hours f-13">
                            <span>{{ $totalTimeFormatted }}</span>
                        </div>
                    </div>
                    <div class="border rounded p-3 bg-light">
                        <p class="mb-0">{{ $endTime != '' ? $endTime->translatedFormat(company()->time_format) : '' }}
                            @if (isset($notClockedOut))
                                (@lang('modules.attendance.currentTime'))
                            @endif
                        </p>
                    </div>

                </div>
            </x-cards.data>
        </div>
        <div class="col-md-6">

            <x-cards.data :title="__('modules.employees.activity')">

                <div class="recent-activity h-auto">
                    @foreach ($attendanceActivity->reverse() as $item)
                        <div class="row res-activity-box" id="timelogBox{{ $item->aId }}">
                            <ul class="res-activity-list col-md-9">
                                <li>
                                    <p class="mb-0">@lang('modules.attendance.clock_in')
                                        @if (!is_null($item->employee_shift_id))
                                            @if ($item->shift->shift_name != 'Day Off')
                                                <span class="badge badge-info ml-2" style="background-color: {{ $item->shift->color }}">{{ $item->shift->shift_name }}</span>
                                            @else
                                                <span class="badge badge-secondary ml-2" >{{ __('modules.attendance.' . str($attendanceSettings->shift_name)->camel()) }}</span>
                                            @endif
                                        @endif
                                    </p>
                                    <p class="res-activity-time">
                                        <i class="fa fa-clock"></i>
                                        {{ $item->clock_in_time->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}

                                        @if ($item->work_from_type != '')
                                            @if ($item->work_from_type == 'other')
                                                <i class="fa fa-map-marker-alt ml-2"></i>
                                                {{ $item->location }} {{ $item->working_from != '' ? '(' . $item->working_from . ')' : ''  }}
                                            @else
                                                <i class="fa fa-map-marker-alt ml-2"></i>
                                                {{ $item->location }} ({{$item->work_from_type}})
                                            @endif
                                        @endif

                                        @if ($item->late == 'yes')
                                            <i class="fa fa-exclamation-triangle ml-2"></i>
                                            @lang('modules.attendance.late')
                                        @endif

                                        @if ($item->half_day == 'yes')
                                            <i class="fa fa-sign-out-alt ml-2"></i>
                                            @lang('modules.attendance.halfDay')
                                            <span>
                                                @if($item->half_day_type == 'first_half')
                                                    ( @lang('modules.leaves.1stHalf') )
                                                @elseif ($item->half_day_type == 'second_half')
                                                    ( @lang('modules.leaves.2ndHalf') )
                                                @else

                                                @endif
                                            </span>
                                        @endif

                                    </p>
                                </li>
                                <li>
                                    <p class="mb-0">@lang('modules.attendance.clock_out')</p>
                                    <p class="res-activity-time">
                                        <i class="fa fa-clock"></i>
                                        @if (!is_null($item->clock_out_time))
                                            {{ $item->clock_out_time->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                            @if($item->auto_clock_out)
                                                <i class="fa fa-sign-out-alt ml-2"></i>
                                                @lang('modules.attendance.autoClockOut')
                                            @endif
                                        @else
                                            @lang('modules.attendance.notClockOut')
                                        @endif
                                    </p>
                                </li>
                            </ul>

                        </div>
                    @endforeach

                </div>
            </x-cards.data>
        </div>
    </div>

</div>

<div class="modal-footer">
    <button type="button" class="mr-3 rounded btn-cancel" data-dismiss="modal">@lang('app.cancel')</button>
    <button type="button" onclick="clockOut()" class="rounded btn-danger"><i
        class="icons icon-login mr-2"></i>@lang('modules.attendance.clock_out')</button>
</div>
<script>

 

</script>
