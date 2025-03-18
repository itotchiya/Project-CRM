@forelse ($dateWiseData as $key => $dateData)
    @php
        $currentDate = \Carbon\Carbon::parse($key);
    @endphp
    @if (isset($dateData['attendance']) && ($dateData['attendance'] == true) && $dateData['leave'] != true)

        <tr>
            <td>
                <div class="media-body">
                    <h5 class="mb-0 f-13">{{ $currentDate->translatedFormat(company()->date_format) }}
                    </h5>
                    <p class="mb-0 f-13 text-dark-grey">
                        <label class="badge badge-secondary">{{ $currentDate->translatedFormat('l') }}</label>
                    </p>
                </div>
            </td>
            <td>
                <span class="badge badge-success">@lang('modules.attendance.present')</span>
            </td>
            <td colspan="2">
                <x-table class="mb-0 rounded table table-bordered table-hover">
                    @foreach ($dateData['attendance'] as $attendance)
                        <tr>
                            <td width="50%">
                                {{ $attendance->clock_in_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}

                                @if ($attendance->late == 'yes')
                                    <span class="text-dark-grey"><i class="fa fa-exclamation-triangle ml-2"></i>
                                    @lang('modules.attendance.late')</span>
                                @endif

                                @if ($attendance->half_day == 'yes')
                                    <span class="text-dark-grey"><i class="fa fa-sign-out-alt ml-2"></i>
                                    @lang('modules.attendance.halfDay')</span>
                                    <span>
                                        @if($attendance->half_day_type == 'first_half')
                                            ( @lang('modules.leaves.1stHalf') )
                                        @elseif ($attendance->half_day_type == 'second_half')
                                            ( @lang('modules.leaves.2ndHalf') )
                                        @else

                                        @endif
                                    </span>
                                @endif

                                @if ($attendance->work_from_type != '')
                                    @if ($attendance->work_from_type == 'other')
                                        <i class="fa fa-map-marker-alt ml-2"></i>
                                        {{ $attendance->location }} ({{$attendance->working_from}})
                                    @else
                                        <i class="fa fa-map-marker-alt ml-2"></i>
                                        {{ $attendance->location }} ({{$attendance->work_from_type}})
                                    @endif
                                @endif
                            </td>
                            <td width="50%">
                                @if (!is_null($attendance->clock_out_time))
                                    {{ $attendance->clock_out_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                    @if($attendance->auto_clock_out)
                                        <i class="fa fa-sign-out-alt ml-2"></i>
                                        @lang('modules.attendance.autoClockOut')
                                    @endif
                                @else - @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </td>
            <td>
                {{ $attendance->totalTime($attendance->clock_in_time, $attendance->clock_in_time, $attendance->user_id) }}
            </td>
            <td class="text-right pb-2 pr-20">
                <x-forms.button-secondary icon="search" class="view-attendance"
                    data-attendance-id="{{ $attendance->aId }}">
                    @lang('app.details')
                </x-forms.button-secondary>
            </td>

        </tr>
    @else
        <tr>
            <td>
                <div class="media-body">
                    <h5 class="mb-0 f-13">{{ $currentDate->translatedFormat(company()->date_format) }}
                    </h5>
                    <p class="mb-0 f-13 text-dark-grey">
                        <span class="badge badge-secondary">{{ $currentDate->translatedFormat('l') }}</span>
                    </p>
                </div>
            </td>
            <td>
                @if (!$dateData['holiday'] && !$dateData['leave'])
                    <label class="badge badge-danger">@lang('modules.attendance.absent')</label>
                @elseif($dateData['leave'])
                    @if ($dateData['leave']['duration'] == 'half day')
                        <label class="badge badge-primary">@lang('modules.attendance.leave')</label><br><br>
                        <label class="badge badge-warning">@lang('modules.attendance.halfDay')</label>
                    @else
                        <label class="badge badge-primary">@lang('modules.attendance.leave')</label>
                    @endif
                @else
                    <label class="badge badge-secondary">@lang('modules.attendance.holiday')</label>
                @endif
            </td>
            @if (isset($dateData['attendance']) && ($dateData['attendance'] == true))
                <td colspan="2">
                        <x-table class="mb-0 rounded table table-bordered table-hover">
                                @foreach ($dateData['attendance'] as $attendance)
                                    <tr>
                                        <td width="50%">
                                            {{ $attendance->clock_in_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}

                                            @if ($attendance->late == 'yes')
                                                <span class="text-dark-grey"><i class="fa fa-exclamation-triangle ml-2"></i>
                                                @lang('modules.attendance.late')</span>
                                            @endif

                                            @if ($attendance->half_day == 'yes')
                                                <span class="text-dark-grey"><i class="fa fa-sign-out-alt ml-2"></i>
                                                @lang('modules.attendance.halfDay')</span>
                                            @endif

                                            @if ($attendance->work_from_type != '')
                                                @if ($attendance->work_from_type == 'other')
                                                    <i class="fa fa-map-marker-alt ml-2"></i>
                                                    {{ $attendance->location }} ({{$attendance->working_from}})
                                                @else
                                                    <i class="fa fa-map-marker-alt ml-2"></i>
                                                    {{ $attendance->location }} ({{$attendance->work_from_type}})
                                                @endif
                                            @endif
                                        </td>
                                        <td width="50%">
                                            @if (!is_null($attendance->clock_out_time))
                                                {{ $attendance->clock_out_time->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
                                            @else - @endif
                                        </td>
                                    </tr>
                                @endforeach
                        </x-table>
                </td>
                <td>{{ $attendance->totalTime($attendance->clock_in_time, $attendance->clock_in_time, $attendance->user_id) }}</td>
                <td class="text-right pb-2 pr-20">
                    <x-forms.button-secondary icon="search" class="view-attendance"
                        data-attendance-id="{{ $attendance->aId }}">
                        @lang('app.details')
                    </x-forms.button-secondary>
                </td>
            @else
                <td colspan="2">
                    <table width="100%">
                        <tr>
                            <td width="50%">-</td>
                            <td width="50%">-</td>
                        </tr>
                    </table>
                </td>
                <td>-</td>
                <td>-</td>
            @endif
        </tr>
    @endif
@empty
    <tr>
        <td colspan="6">
            <x-cards.no-record icon="calendar" :message="__('messages.noRecordFound')" />
        </td>
    </tr>
@endforelse
