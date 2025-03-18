<nav class="mb-3">
    <ul class="pagination pagination-sm week-pagination">
        @foreach ($weekPeriod->toArray() as $date)
            <li
            @class([
                'page-item',
                'week-timelog-day',
                'active' => ($timelogDate->toDateString() == $date->toDateString()),
            ])
            data-toggle="tooltip" data-original-title="{{ $date->translatedFormat(company()->date_format) }}" data-date="{{ $date->toDateString() }}">
                <a class="page-link" href="javascript:;">{{ $date->isoFormat('dd') }}</a>
            </li>
        @endforeach
    </ul>
</nav>
<div class="progress" style="height: 20px;">
    @php
        $totalDayMinutesPercent = ($dayMinutes > 0) ? floatval((floatval($dayMinutes - $dayBreakMinutes)/$dayMinutes) * 100) : 0;
    @endphp
    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $totalDayMinutesPercent }}%" aria-valuenow="{{ $totalDayMinutesPercent }}" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-original-title="{{ $totalDayMinutes }}"></div>

    <div class="progress-bar bg-grey" role="progressbar" style="width: {{ (100 - $totalDayMinutesPercent) }}%" aria-valuenow="{{ $totalDayMinutesPercent }}" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-original-title="{{ $totalDayBreakMinutes }}"></div>
</div>

<div class="d-flex justify-content-between mt-1 text-dark-grey f-12">
    <small>@lang('app.duration'): {{ $totalDayMinutes }}</small>
    <small>@lang('modules.timeLogs.break'): {{ $totalDayBreakMinutes }}</small>
</div>
