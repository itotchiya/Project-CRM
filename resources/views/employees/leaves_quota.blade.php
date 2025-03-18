<div class="card w-100 rounded-0 border-0 comment">
    <div class="card-horizontal">
        <div class="card-body border-0 px-1 py-1">
            @if ($hasLeaveQuotas)
                <div class="card-text f-14 text-dark-grey text-justify">
                    <x-table class="table-bordered my-3 rounded">
                        <x-slot name="thead">
                            <th>@lang('modules.leaves.leaveType')</th>
                            <th class="text-center">@lang('modules.leaves.noOfLeaves')</th>
                            <th class="text-center">@lang('modules.leaves.monthLimit')</th>
                            <th class="text-center">@lang('app.totalLeavesTaken')</th>
                            <th class="text-center">@lang('modules.leaves.remainingLeaves')</th>
                            <th class="text-center">@lang('modules.leaves.overUtilized')</th>
                            <th class="text-center">@lang('modules.leaves.unusedLeaves')</th>
                        </x-slot>
       
       
                        @foreach ($allowedEmployeeLeavesQuotas as $key => $leavesQuota)
                            <tr @if($leavesQuota->leaveType->deleted_at != null) style="background-color: #c8d3dd !important;" @endif>
                                <td>
                                    <x-status :value="$leavesQuota->leaveType->type_name" :style="'color:'.$leavesQuota->leaveType->color" />
                                        @if($leavesQuota->leaveType->deleted_at != null) ( @lang('app.leaveArchive') )  @endif
                                </td>
                                <td class="text-center">{{ $leavesQuota?->no_of_leaves ?: 0 }}</td>
                                <td class="text-center">{{ ($leavesQuota->leaveType->monthly_limit > 0) ? $leavesQuota->leaveType->monthly_limit : '--' }}</td>
                                <td class="text-center">{{ $leavesQuota->leaves_used }}</td>
                                <td class="text-center">{{ $leavesQuota->leaves_remaining }}</td>
                                <td class="text-center">{{ $leavesQuota->overutilised_leaves }}</td>
                                <td class="text-center">{{ $leavesQuota->unused_leaves }}</td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            @endif

            @if (!$hasLeaveQuotas)
                <x-cards.no-record icon="redo" :message="__('messages.noRecordFound')" />
            @endif
        </div>
    </div>
</div>
