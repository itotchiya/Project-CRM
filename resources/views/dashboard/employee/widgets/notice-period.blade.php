@if (in_array('notice_period_duration', $activeWidgets) && in_array('employees', user_modules()))
    @php
        $currentDay = \Carbon\Carbon::parse(now(company()->timezone)->toDateTimeString())
            ->startOfDay()
            ->setTimezone('UTC');
    @endphp
    <div class="col-sm-12 mt-2">
        @if (in_array('admin', user_roles()))
            <x-cards.data class="e-d-info mb-2" :title="__('modules.dashboard.noticePeriodDuration')" padding="false">
                <x-table>
                    @forelse ($noticePeriod as $noticePrd)
                        @php
                            $noticePeriodEndDate = Carbon\carbon::parse($noticePrd->notice_period_end_date);
                            $noticePeriodStartDate = Carbon\carbon::parse($noticePrd->notice_period_start_date);
                            $diffInDays = $noticePeriodEndDate->copy()->diffForHumans($currentDay);
                        @endphp
                        <tr>
                            <td class="pl-20">
                                <x-employee :user="$noticePrd->user"/>
                            </td>

                            <td class="pr-20 text-right">
                                @if ($noticePeriodEndDate->setTimezone(company()->timezone)->isToday())
                                    <span class="badge badge-light text-success p-2">@lang('app.today')</span>
                                @elseif ($noticePeriodEndDate->diffInDays($currentDay) <= 7)
                                    <span class="badge badge-light text-warning p-2">{{ $diffInDays }}</span>
                                @else
                                    <span class="badge badge-light p-2">{{ $diffInDays }}</span>
                                @endif

                                <br>

                                <span class="f-12">
                                    {{ $noticePeriodStartDate->translatedFormat($company->date_format) . ' - ' . $noticePeriodEndDate->translatedFormat($company->date_format) }}
                                </span>
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
            @if ($noticePeriod)
                <x-cards.data class="e-d-info mb-2" :title="__('modules.dashboard.noticePeriodDuration')"
                              padding="false">
                    <x-table>
                        <tr>
                            @php
                                $noticePeriodStartDate = Carbon\carbon::parse($noticePeriod->notice_period_start_date);
                                $noticePeriodEndDate = Carbon\carbon::parse($noticePeriod->notice_period_end_date);
                                $diffInDays = $noticePeriodEndDate->copy()->diffForHumans($currentDay);
                            @endphp
                            <td class="pl-20">
                                @if ($noticePeriodEndDate->setTimezone(company()->timezone)->isToday())
                                    <span
                                        class="text-success f-12">{{ $noticePeriodStartDate->translatedFormat($company->date_format) . ' - ' . $noticePeriodEndDate->translatedFormat($company->date_format) }}</span>
                                @elseif ($noticePeriodEndDate->diffInDays($currentDay) <= 7)
                                    <span
                                        class="text-warning f-12">{{ $noticePeriodStartDate->translatedFormat($company->date_format) . ' - ' . $noticePeriodEndDate->translatedFormat($company->date_format) }}</span>
                                @else
                                    <span
                                        class="f-12">{{ $noticePeriodStartDate->translatedFormat($company->date_format) . ' - ' . $noticePeriodEndDate->translatedFormat($company->date_format) }}</span>
                                @endif
                            </td>

                            <td class="pr-20 text-right">
                                @if ($noticePeriodEndDate->setTimezone(company()->timezone)->isToday())
                                    <span class="badge badge-light text-success p-2">@lang('app.today')</span>
                                @elseif ($noticePeriodEndDate->diffInDays($currentDay) <= 7)
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
