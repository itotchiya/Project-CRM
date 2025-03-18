@if (in_array('probation_date', $activeWidgets) && in_array('employees', user_modules()))
    @php
        $currentDay = \Carbon\Carbon::parse(now(company()->timezone)->toDateTimeString())
            ->startOfDay()
            ->setTimezone('UTC');
    @endphp
    <div class="col-sm-12">
        @if (in_array('admin', user_roles()))
            <x-cards.data class="e-d-info mb-2" :title="__('modules.dashboard.probationDate')" padding="false">
                <x-table>
                        @forelse ($probations as $probation)
                            @php
                                $probationDate = Carbon\carbon::parse($probation->probation_end_date);
                                $diffInDays = $probationDate->copy()->diffForHumans($currentDay);
                            @endphp
                            <tr>
                                <td class="pl-20">
                                    <x-employee :user="$probation->user"/>
                                </td>

                                <td class="pr-20 text-right">
                                    @if ($probationDate->setTimezone(company()->timezone)->isToday())
                                        <span class="badge badge-light text-success p-2">@lang('app.today')</span>
                                    @elseif($probationDate->diffInDays($currentDay) <= 7)
                                        <span class="badge badge-light text-warning p-2">{{ $diffInDays }}</span>
                                    @else
                                        <span class="badge badge-light p-2">{{ $diffInDays }}</span>
                                    @endif

                                    <br>
                                    @if ($probationDate->setTimezone(company()->timezone)->isToday())
                                        <span class="text-success f-11">@lang('messages.probationMessage')
                                            {{ $probationDate->translatedFormat($company->date_format) }}</span>
                                    @elseif($probationDate->diffInDays($currentDay) <= 7)
                                        <span class="text-warning f-11">@lang('messages.probationMessage')
                                            {{ $probationDate->translatedFormat($company->date_format) }}</span>
                                    @else
                                        <span class="f-11">@lang('messages.probationMessage')
                                            {{ $probationDate->translatedFormat($company->date_format) }}</span>
                                    @endif

                                </td>
                            </tr>
                        @empty
                            <span class="shadow-none">
                            <x-cards.no-record icon="list" :message="__('messages.noRecordFound')"/>
                        </span>
                        @endforelse
                </x-table>
            </x-cards.data>
        @else
            @if ($probation)
                <x-cards.data class="e-d-info mb-2" :title="__('modules.dashboard.probationDate')" padding="false">
                    <x-table>
                        <tr>
                            @php
                                $probationDate = Carbon\carbon::parse($probation->probation_end_date);
                                $diffInDays = $probationDate->copy()->diffForHumans($currentDay);
                            @endphp
                            <td class="pl-20">
                                @if ($probationDate->setTimezone(company()->timezone)->isToday())
                                    <span class="text-success f-12">@lang('messages.probationMessage')
                                        {{ $probationDate->translatedFormat($company->date_format) }}</span>
                                @elseif($probationDate->diffInDays($currentDay) <= 7)
                                    <span class="text-warning f-12">@lang('messages.probationMessage')
                                        {{ $probationDate->translatedFormat($company->date_format) }}</span>
                                @else
                                    <span class="f-12">@lang('messages.probationMessage')
                                        {{ $probationDate->translatedFormat($company->date_format) }}</span>
                                @endif
                            </td>

                            <td class="pr-20 text-right">
                                @if ($probationDate->setTimezone(company()->timezone)->isToday())
                                    <span class="badge badge-light text-success p-2">@lang('app.today')</span>
                                @elseif($probationDate->diffInDays($currentDay) <= 7)
                                    <span class="badge badge-light text-warning p-2">{{ $diffInDays }}</span>
                                @else
                                    <span class="badge badge-light p-2">{{ $diffInDays }}</span>
                                @endif
                            </td>
                        </tr>
                    </x-table>
                </x-cards.data>
            @endif
        @endif
    </div>
@endif
