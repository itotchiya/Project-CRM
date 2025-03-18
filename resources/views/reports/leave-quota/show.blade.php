<div class="modal-header">
    <h5 class="modal-title">@lang('app.menu.leavesQuota') ({{ $employee->name}})</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body pt-0">
    <div class="row mb-4 bg-additional-grey py-2">
        <div class="col-lg-4">
            <x-cards.widget icon="sign-out-alt" :title="__('modules.leaves.remainingLeaves')" :value="$allowedLeaves" />
        </div>
    </div>

    <div class="p-1">
            @include('employees.leaves_quota')
    </div>
</div>
