@php
$editLeavePermission = user()->permission('edit_leave');
$deleteLeavePermission = user()->permission('delete_leave');
$approveRejectPermission = user()->permission('approve_or_reject_leaves');
@endphp

<div id="leave-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('app.leavesDetails')</h3>
                            <div class="f-10 text-lightest">@lang('app.applyDate') - {{ $leave->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format .' ' . company()->time_format) }}</div>
                        </div>
                        <div class="col-md-2 col-2 text-right">
                            <div class="dropdown">
                                @if ($leave->status == 'pending')
                                    <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">
                                        @php
                                            $rejectActionAdded = false;
                                            $approvedActionAdded = false;
                                        @endphp
                                            @if ($reportingTo && $leave->user_id != user()->id && !in_array('admin', user_roles()))
                                                @if(!$rejectActionAdded && $leave->manager_status_permission == '' && !($reportingPermission == 'cannot-approve'))
                                                    <a data-leave-id="{{ $leave->id }}" data-leave-action="rejected" data-user-id="{{ $leave->user_id }}" data-leave-type-id="{{ $leave->leave_type_id }}" class="dropdown-item leave-action-reject" href="javascript:;">
                                                            <i class="fa fa-times mr-2"></i>
                                                            @lang('app.reject')
                                                    </a>
                                                    @php $rejectActionAdded = true; @endphp
                                                @endif

                                                @if (!$approvedActionAdded && $reportingPermission == 'approved' && $leave->manager_status_permission == '')
                                                    <a class="dropdown-item leave-action-approved" data-leave-id="{{ $leave->id }}" data-leave-action="approved" data-user-id="{{ $leave->user_id }}" data-leave-type-id="{{ $leave->leave_type_id }}" href="javascript:;">
                                                        <i class="fa fa-check mr-2"></i>
                                                        @lang('app.approve')
                                                    </a>
                                                    @php $approvedActionAdded = true; @endphp
                                                @elseif($reportingPermission == 'pre-approve' && !$leave->manager_status_permission)
                                                    <a data-leave-id="{{ $leave->id }}"
                                                            data-leave-action="pre-approve" data-user-id="{{ $leave->user_id }}" data-leave-type-id="{{ $leave->leave_type_id }}" class="dropdown-item leave-action-preapprove" href="javascript:;">
                                                            <i class="fa fa-check mr-2"></i>
                                                            @lang('app.preApprove')
                                                    </a>
                                                @endif
                                            @endif

                                            @if ($leave->status == 'pending' && $approveRejectPermission == 'all')
                                                @if(!$approvedActionAdded)
                                                    <a class="dropdown-item leave-action-approved" data-leave-id="{{ $leave->id }}" data-leave-action="approved" data-user-id="{{ $leave->user_id }}" data-leave-type-id="{{ $leave->leave_type_id }}" href="javascript:;">
                                                        <i class="fa fa-check mr-2"></i>
                                                        @lang('app.approve')
                                                    </a>
                                                    @php $approvedActionAdded = true; @endphp
                                                @endif
                                                @if(!$rejectActionAdded)
                                                    <a data-leave-id="{{ $leave->id }}" data-leave-action="rejected" data-user-id="{{ $leave->user_id }}" data-leave-type-id="{{ $leave->leave_type_id }}" class="dropdown-item leave-action-reject" href="javascript:;">
                                                            <i class="fa fa-times mr-2"></i>
                                                            @lang('app.reject')
                                                    </a>
                                                    @php $rejectActionAdded = true; @endphp
                                                @endif
                                            @endif

                                            @if ($editLeavePermission == 'all'
                                            || ($editLeavePermission == 'added' && user()->id == $leave->added_by)
                                            || ($editLeavePermission == 'owned' && user()->id == $leave->user_id)
                                            || ($editLeavePermission == 'both' && (user()->id == $leave->user_id || user()->id == $leave->added_by)))
                                                <a class="dropdown-item openRightModal"
                                                data-redirect-url="{{ url()->previous() }}"
                                                href="{{ route('leaves.edit', $leave->id) }}"><i class="fa fa-edit mr-2"></i> @lang('app.edit')</a>
                                            @endif

                                            @if (($deleteLeavePermission == 'all'
                                            || ($deleteLeavePermission == 'added' && user()->id == $leave->added_by)
                                            || ($deleteLeavePermission == 'owned' && user()->id == $leave->user_id)
                                            || ($deleteLeavePermission == 'both' && (user()->id == $leave->user_id || user()->id == $leave->added_by))))
                                                <a class="dropdown-item delete-leave"><i class="fa fa-trash mr-2"></i> @lang('app.delete')</a>
                                            @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $leaveType = '<span class="badge badge-success" style="background-color:' . $leave->type->color . '">' . $leave->type->type_name . '</span>';

                        if ($leave->status == 'approved') {
                            $class = 'text-light-green';
                            $status = __('app.approved');
                        } elseif ($leave->status == 'pending') {
                            $class = 'text-yellow';
                            $status = __('app.pending');
                        } else {
                            $class = 'text-red';
                            $status = __('app.rejected');
                        }
                        $paidStatus = '<i class="fa fa-circle mr-1 ' . $class . ' f-10"></i> ' . $status;

                        $reject_reason = !is_null($leave->reject_reason) ? $leave->reject_reason : '--';

                        $approve_reason = !is_null($leave->approve_reason) ? $leave->approve_reason : '--';

                    @endphp

                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                            @lang('modules.leaves.applicantName')</p>
                        <p class="mb-0 text-dark-grey f-14">
                            <x-employee :user="$leave->user" />
                        </p>
                    </div>

                    <x-cards.data-row :label="__('modules.leaves.leaveDate')" :value="$leave->leave_date->translatedFormat(company()->date_format)"
                        html="true" />

                    <x-cards.data-row :label="__('modules.leaves.leaveType')" :value="$leaveType" html="true" />

                    @php
                        if ($leave->paid == 1) {
                            $leavePaidStatus = '<span class="badge badge-success">' . __('app.paid') . '</span>';

                        } else {
                            $leavePaidStatus = '<span class="badge badge-danger">' . __('app.unpaid') . '</span>';

                        }
                    @endphp

                    <x-cards.data-row :label="__('app.paid')" :value="$leavePaidStatus" html="true" />

                    @if ($leave->duration == 'half day')

                        <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('app.duration')</p>
                            <p class="mb-0 text-dark-grey f-14">
                                @lang('modules.leaves.halfDay')

                                @if (!is_null($leave->half_day_type))
                                    <span class='badge badge-secondary ml-1'>{{ ($leave->half_day_type == 'first_half') ? __('modules.leaves.firstHalf') : __('modules.leaves.secondHalf') }} </span>
                                @endif
                            </p>
                        </div>

                    @endif

                    <x-cards.data-row :label="__('modules.leaves.reason')" :value="$leave->reason" html="true" />

                    @if (!is_null($leave->manager_status_permission))
                        <x-cards.data-row :label="__('modules.leaves.statusReport')" :value="$leave->manager_status_permission==='pre-approve' ? __('modules.leaves.preApproved') : ''" html="true" />
                    @endif

                    <x-cards.data-row :label="__('app.status')" :value="$paidStatus" html="true" />

                    @if (!is_null($leave->approved_by))
                        <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('modules.leaves.approvedBy')</p>
                            <p class="mb-0 text-dark-grey f-14">
                                <x-employee :user="$leave->approvedBy" />
                            </p>
                        </div>
                    @endif

                    @if (!is_null($leave->approved_at))
                        <x-cards.data-row :label="__('modules.leaves.approvedAt')" :value="$leave->approved_at->timezone(company()->timezone)->translatedFormat(company()->date_format .' ' . company()->time_format)" />
                    @endif

                    @if ($leave->status == 'rejected')
                        <x-cards.data-row :label="__('messages.reasonForLeaveRejection')" :value="$reject_reason"
                            html="true" />
                    @endif

                    @if ($leave->status == 'approved')
                        <x-cards.data-row :label="__('messages.reasonForLeaveApproval')" :value="$approve_reason"
                            html="true" />
                    @endif
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                            @lang('app.file')</p>
                        <p class="mb-0 text-dark-grey f-14">
                            <div div class="d-flex flex-wrap mt-3" id="leave-file-list">
                                @forelse($leave->files as $file)
                                    <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
                                            <x-file-view-thumbnail :file="$file"></x-file-view-thumbnail>

                                            <x-slot name="action">
                                                <div class="dropdown ml-auto file-action">
                                                    <button class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-ellipsis-h"></i>
                                                    </button>

                                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                                        aria-labelledby="dropdownMenuLink" tabindex="0">
                                                        @if ($file->icon == 'images')
                                                            <a class="img-lightbox cursor-pointer d-block text-dark-grey f-13 pt-3 px-3" data-image-url="{{ $file->file_url }}" href="javascript:;">@lang('app.view')</a>
                                                        @else
                                                            <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 " target="_blank" href="{{ $file->file_url }}">@lang('app.view')</a>
                                                        @endif
                                                            <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                                                href="{{ route('leave-files.download', md5($file->id)) }}">@lang('app.download')</a>

                                                            <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-file"
                                                                data-row-id="{{ $file->id }}" href="javascript:;">@lang('app.delete')</a>
                                                    </div>
                                                </div>
                                            </x-slot>

                                    </x-file-card>
                                @empty
                                <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file" />
                                @endforelse
                            </div>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- ROW END -->

<script>
    $('body').on('click', '.delete-leave', function() {
        var type ='delete-single';
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('leaves.destroy', $leave->id) }}";

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        'type': type,
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if(response.status == "success"){
                            if(response.redirectUrl == undefined){
                                window.location.reload();
                            } else{
                                window.location.href = response.redirectUrl;
                            }
                        }
                    }
                });
            }
        });
    });

    $('body').on('click', '.leave-action-preapprove', function() {
        var action = $(this).data('leave-action');
        var leaveId = $(this).data('leave-id');
        var url = "{{ route('leaves.pre_approve_leave') }}";

        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.changeLeaveStatusConfirmation')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirm')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        'action': action,
                        'leaveId': leaveId,
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });

    $('body').on('click', '.delete-file', function() {
        var id = $(this).data('row-id');
        Swal.fire({
        title: "@lang('messages.sweetAlertTitle')",
        text: "@lang('messages.recoverRecord')",
        icon: 'warning',
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: "@lang('messages.confirmDelete')",
        cancelButtonText: "@lang('app.cancel')",
        customClass: {
            confirmButton: 'btn btn-primary mr-3',
            cancelButton: 'btn btn-secondary'
        },
        showClass: {
            popup: 'swal2-noanimation',
            backdrop: 'swal2-noanimation'
        },
        buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('leave-files.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('#leave-file-list').html(response.view);
                        }
                    }
                });
            }
        });
    });

    $('body').on('click', '.leave-action-approved', function() {
        let action = $(this).data('leave-action');
        let leaveId = $(this).data('leave-id');
        var type = $(this).data('type');
            if(type == undefined){
                var type = 'single';
            }
        let searchQuery = "?leave_action=" + action + "&leave_id=" + leaveId + "&type=" + type;
        let url = "{{ route('leaves.show_approved_modal') }}" + searchQuery;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.leave-action-reject', function() {
        let action = $(this).data('leave-action');
        let leaveId = $(this).data('leave-id');
        var type = $(this).data('type');
            if(type == undefined){
                var type = 'single';
            }
        let searchQuery = "?leave_action=" + action + "&leave_id=" + leaveId + "&type=" + type;
        let url = "{{ route('leaves.show_reject_modal') }}" + searchQuery;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>
