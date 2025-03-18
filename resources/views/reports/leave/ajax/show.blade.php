<div class="row mx-0 py-4 bg-additional-grey">
    @foreach ($leave_types as $leave_type)
        @php
            $totalCount = 0;
            foreach ($leave_type->leaves as $leave) {
                // Adjust count based on leave duration
                $totalCount += ($leave->duration == 'half day') ? 0.5 : 1;
            }
        @endphp
        <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
            <x-cards.widget :title="$leave_type->type_name" :value="$totalCount" icon="calendar" />
        </div>
    @endforeach
</div>
<div class="table-responsive">
    <x-table>
        <x-slot name="thead">
            <th width="20%">@lang('modules.leaves.leaveType')</th>
            <th width="20%">@lang('app.date')</th>
            <th width="20%">@lang('app.paid')</th>
            <th>@lang('modules.leaves.reason')</th>
        </x-slot>
        @foreach ($leave_types as $item)
            @foreach ($item->leaves as $key => $leave)
                <tr>
                    <td>
                        <x-status :style="'color: '.$leave->type->color" :value="$leave->type->type_name" />
                        {!! $leave->duration == 'half day' ? '<span class="badge badge-inverse">' . __('modules.leaves.halfDay') . '</span>' : '' !!}
                    </td>
                    <td>
                        {{ $leave->leave_date->translatedFormat(company()->date_format) }}
                    </td>
                    <td>
                        @if ($leave->paid == 1)
                            <span class="badge badge-success">{{ __('app.paid') }}</span>
                        @else
                            <span class="badge badge-danger">{{ __('app.unpaid') }}</span>
                        @endif
                        @if ($leave->over_utilized == 1)
                            <br>({{ __('modules.leaves.overUtilized') }})
                        @endif
                    </td>
                    <td>
                        {{ $leave->reason }}
                    </td>
                </tr>
            @endforeach
        @endforeach

    </x-table>
</div>
