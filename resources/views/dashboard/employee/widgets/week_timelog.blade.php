@if (in_array('week_timelog', $activeWidgets) && $sidebarUserPermissions['view_timelogs'] != 5 && $sidebarUserPermissions['view_timelogs'] != 'none' && in_array('timelogs', user_modules()))
    <div @class(['mb-3', 'col-md-6' => (in_array('lead', $activeWidgets) && $leadAgent), 'col-md-12' => !(in_array('lead', $activeWidgets) && $leadAgent)])>
        <div
            class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
            <div class="d-block  w-100">
                @php
                    $totalWeekTime = $weekWiseTimelogs - $weekWiseTimelogBreak;
                    // Convert total minutes to hours and minutes
                    $hours = intdiv($totalWeekTime, 60);
                    $minutes = $totalWeekTime % 60;

                    // Format output based on hours and minutes
                    $weekTimeLog = $hours > 0
                        ? $hours . 'h' . ($minutes > 0 ? ' ' . sprintf('%02dm', $minutes) : '')
                        : ($minutes > 0 ? sprintf('%dm', $minutes) : '0s');
                @endphp
                <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">@lang('modules.dashboard.weekTimelog') <span class="badge badge-secondary ml-1 f-10">{{ $weekTimeLog . ' ' . __('modules.timeLogs.thisWeek') }}</span></h5>

                <div id="weekly-timelogs">
                    <nav class="mb-3">
                        <ul class="pagination pagination-sm week-pagination">
                            @foreach ($weekPeriod->toArray() as $date)
                                <li
                                    @class([
                                        'page-item',
                                        'week-timelog-day',
                                        'active' => (now(company()->timezone)->toDateString() == $date->toDateString()),
                                    ])
                                    data-toggle="tooltip" data-original-title="{{ $date->translatedFormat(company()->date_format) }}" data-date="{{ $date->toDateString() }}">
                                    <a class="page-link" href="javascript:;">{{ $date->isoFormat('dd') }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>

                    @php
                        $totalDayMinutesPercent = ($dayMinutes > 0) ? floatval((floatval($dayMinutes - $dayBreakMinutes)/$dayMinutes) * 100) : 0;
                    @endphp
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $totalDayMinutesPercent }}%" aria-valuenow="{{ $totalDayMinutesPercent }}" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-original-title="{{ $totalDayMinutes }}"></div>

                        <div class="progress-bar bg-grey" role="progressbar" style="width: {{ (100 - $totalDayMinutesPercent) }}%" aria-valuenow="{{ $totalDayMinutesPercent }}" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-original-title="{{ $totalDayBreakMinutes }}"></div>
                    </div>

                    <div class="d-flex justify-content-between mt-1 text-dark-grey f-12">
                        <small>@lang('app.duration'): {{ $totalDayMinutes }}</small>
                        <small>@lang('modules.timeLogs.break'): {{ $totalDayBreakMinutes }}</small>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endif
