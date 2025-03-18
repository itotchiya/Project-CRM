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
                        </x-slot>
                        @php
                            $processedLeaveTypes = [];
                        @endphp
                        @foreach ($employeeLeavesQuotas as $key => $leavesQuota)
                            @php
                                if (in_array($leavesQuota->leave_type_id, $processedLeaveTypes)) {
                                    continue; // Skip this iteration if already processed
                                }
                            
                                // Add the current leave_type_id to the array
                                $processedLeaveTypes[] = $leavesQuota->leave_type_id;

                                
                                $remleaves =  ($leaveCounts[$leavesQuota->leave_type_id] > ($leavesQuota?->no_of_leaves ?: 0)) 
                                                    ? 0 : ($leavesQuota?->no_of_leaves ?: 0) - ($leaveCounts[$leavesQuota->leave_type_id] ?: 0) ;  
                                $noofleaves = ($leaveCounts[$leavesQuota->leave_type_id] > ($leavesQuota?->no_of_leaves ?: 0)) 
                                                    ? $leaveCounts[$leavesQuota->leave_type_id] : ($leavesQuota?->no_of_leaves ?: 0);
                            @endphp

                            @if($leavesQuota->leaveType && $leavesQuota->leaveType->leaveTypeCondition($leavesQuota->leaveType,  $employee)
                                && ($leavesQuota->leaveType->deleted_at == null || $leavesQuota->leaves_used > 0)
                            )
                            <tr @if($leavesQuota->leaveType->deleted_at != null) style="background-color: #c8d3dd !important;" @endif>
                                <td>
                                    <x-status :value="$leavesQuota->leaveType->type_name" :style="'color:'.$leavesQuota->leaveType->color" />
                                        @if($leavesQuota->leaveType->deleted_at != null) ( @lang('app.leaveArchive') )  @endif
                                </td>
                                <td class="text-center">{{ $leavesQuota?->no_of_leaves ?: 0 }}</td>
                                {{-- <td class="text-center">{{ $noofleaves }}</td> --}}
                                <td class="text-center">{{ ($leavesQuota->leaveType->monthly_limit > 0) ? $leavesQuota->leaveType->monthly_limit : '--' }}</td>
                                <td class="text-center">{{ $leaveCounts[$leavesQuota->leave_type_id] }}{{-- {{ $leavesQuota->leaves_used }} --}}</td>
                                <td class="text-center">{{ $remleaves }}</td>
                                <td class="text-center">{{ ($leaveCounts[$leavesQuota->leave_type_id] - $leavesQuota?->no_of_leaves) > 0 ? ($leaveCounts[$leavesQuota->leave_type_id] - $leavesQuota?->no_of_leaves) : 0 }}</td>
                            </tr>
                            @endif
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
