@php
$updateLeaveQuotaPermission = user()->permission('update_leaves_quota');
@endphp

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active mt-5" role="tabpanel" aria-labelledby="nav-email-tab">

    <x-alert type="info" icon="info-circle">
        @lang('messages.leaveQuotaShowing', ['start_date' => $leaveStartDate, 'end_date' => $leaveEndDate])
    </x-alert>

    <div class="row mb-4">
        <div class="col-lg-4">
            <x-cards.widget icon="sign-out-alt" :title="__('modules.leaves.remainingLeaves')" :value="$allowedLeaves" />
        </div>
    </div>


    <x-cards.data :title="__('app.menu.leavesQuota')">
        @if ($updateLeaveQuotaPermission == 'all')

            <div class="row">
                <div class="col-md-12">
                    <a class="f-15 f-w-500" href="javascript:;" id="renew-contract"><i
                            class="icons icon-settings font-weight-bold mr-1"></i>
                        @lang('app.manage')</a>
                </div>
            </div>

            <x-form id="save-renew-data-form" class="d-none">

                <div class="row">
                    <div class="col-md-12">
                        <x-table class="table-bordered mb-3 rounded">
                            <x-slot name="thead">
                                <th>@lang('modules.leaves.leaveType')</th>
                                <th>@lang('modules.leaves.noOfLeaves')</th>
                                <th class="text-right"></th>
                                <th class="text-right">@lang('app.action')</th>
                            </x-slot>

                            @foreach ($employeeLeavesQuotas as $key => $leavesQuota)
                                @if($leavesQuota->leaveType && $leavesQuota->leaveType->leaveTypeCondition($leavesQuota->leaveType,  $employee)
                                        && ($leavesQuota->leaveType->deleted_at == null || $leavesQuota->leaves_used > 0)
                                    )
                                    <tr>
                                        <td>
                                            <x-status :value="$leavesQuota->leaveType->type_name" :style="'color:'.$leavesQuota->leaveType->color" />
                                        </td>
                                        <td> <input type="number" min="0" value="{{ $leavesQuota?->no_of_leaves ?: 0 }}"
                                                class="form-control height-35 f-14 leave-count-{{ $leavesQuota->id }}">
                                        </td>
                                        <td >
                                            <div class="form-check float-right">
                                            <x-forms.checkbox :fieldLabel="__('modules.leaves.leaveTypeImpacttrue')"
                                                    fieldName="allowed_impact" fieldId="allowed_probation-{{ $leavesQuota->id }}" fieldValue="0" fieldRequired="false"
                                                    :checked="$leavesQuota->leave_type_impact == 1"
                                                    :popover="__('modules.leaves.leavemanageimpact')"/>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <button type="button" data-type-id="{{ $leavesQuota->id }}"
                                                class="btn btn-sm btn-primary btn-outline update-category">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            @if (!$hasLeaveQuotas)
                                <tr>
                                    <td colspan="3">
                                        <x-cards.no-record icon="redo" :message="__('messages.noRecordFound')" />
                                    </td>
                                </tr>
                            @endif
                        </x-table>
                    </div>
                </div>

                <div class="w-100 justify-content-end d-flex mt-2">
                    <x-forms.button-cancel id="cancel-renew" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </div>
            </x-form>
        @endif


        <div class="d-flex flex-wrap justify-content-between" id="comment-list">
            @include('employees.leaves_quota')
        </div>

    </x-cards.data>
</div>
<!-- TAB CONTENT END -->

<script>
    $(document).ready(function() {
        
        $(document).on('keydown', 'input', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                return false;
            }
        });

        setTimeout(function(){
            $('[data-toggle="popover"]').popover(); 
        }, 300);

        $('#renew-contract').click(function() {
            $(this).closest('.row').addClass('d-none');
            $('#save-renew-data-form').removeClass('d-none');
        });

        $('#cancel-renew').click(function() {
            $('#save-renew-data-form').addClass('d-none');
            $('#renew-contract').closest('.row').removeClass('d-none');
        });

        $('.update-category').click(function() {
            var id = $(this).data('type-id');
            var leaves = $('.leave-count-' + id).val();
            let editLeaveimpact = null;

            if ($('#allowed_probation-' + id).is(':checked')) {
                editLeaveimpact = 1;
            } else {
                editLeaveimpact = 0;
            }

            var url = "{{ route('employee-leaves.update', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                type: 'POST',
                url: url,
                data: {
                    '_method': 'PUT',
                    '_token': token,
                    'leaves': leaves,
                    'leaveimpact' : editLeaveimpact,
                },
                success: function(response) {
                    if (response.status == "success") {
                        window.location.reload();
                    }
                }
            });
        });

    });

</script>
