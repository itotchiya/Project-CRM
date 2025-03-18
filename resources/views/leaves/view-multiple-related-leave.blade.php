<style>
    .task_view{
        border: 0px !important;
    }
    .action-hover:hover{
        background-color: #ffffff !important;
    }
</style>
<div class="modal-header">
    <h5 class="modal-title">@lang('app.totalLeave') ( {{$multipleLeaves[0]->user->name}} )</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-0">
   @include('leaves.multiple-leave-table')
</div>
<div class="modal-footer">
        @php
            if($pendingCountLeave != count($multipleLeaves)){
                $approveTitle = __('modules.leaves.approveRemaining');
                $rejectTitle = __('modules.leaves.rejectRemaining');
            }
            else {
                $approveTitle = __('modules.leaves.approveAll');
                $rejectTitle = __('modules.leaves.rejectAll');
            }
        @endphp

        @if ($pendingCountLeave > 0 && ($approveRejectPermission == 'all' || ($leaveSetting->manager_permission != 'cannot-approve' && user()->id == $multipleLeaves->first()->user->employeeDetails->reporting_to)))

        @if (user()->id != $multipleLeaves->first()->user->employeeDetails->reporting_to)
            <a class="btn btn-primary rounded f-14 p-2 leave-action-approved" data-leave-id="{{ $multipleLeaves->first()->unique_id }}"
                data-leave-action="approved" data-type="approveAll" class="mr-3" icon="check" href="javascript:;">
                <i class="fa fa-check mr-2"></i>{{$approveTitle}}</a>
        @elseif($leaveSetting->manager_permission == 'pre-approve' && user()->id == $multipleLeaves->first()->user->employeeDetails->reporting_to &&  !$multipleLeaves->pluck('manager_status_permission')->every(fn($status) => $status === 'pre-approve'))
            <a class="btn btn-primary rounded f-14 p-2 leave-action-preapprove" 
                data-leave-id="{{ $multipleLeaves->first()->id }}"
                data-leave-uid="{{ $multipleLeaves->first()->unique_id }}"
                data-user-id="{{ $multipleLeaves->first()->user_id}}"
                data-leave-type-id="{{ $multipleLeaves->first()->leave_type_id}}"
                data-leave-action="pre-approve" data-type="approveAll" class="mr-3" icon="check" href="javascript:;">
                <i class="fa fa-check mr-2"></i>@lang('app.preApprove') @lang('app.all')</a>
        @endif
            <a class="btn btn-secondary rounded f-14 p-2 leave-action-reject" data-leave-id="{{ $multipleLeaves->first()->unique_id }}"
                data-leave-action="rejected" data-type="rejectAll" class="mr-3" icon="check" href="javascript:;">
                <i class="fa fa-times mr-2"></i>{{$rejectTitle}}</a>
        @endif

    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
</div>
