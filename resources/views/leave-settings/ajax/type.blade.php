

<!-- LEAVE SETTING START -->
<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">

    <div class="table-responsive">
        <x-table class="table-bordered">
            <x-slot name="thead">
                <th>@lang('modules.leaves.leaveType')</th>
                <th>@lang('modules.leaves.leaveAllotmentType')</th>
                <th>@lang('modules.leaves.noOfLeaves')</th>
                <th>@lang('modules.leaves.monthLimit')</th>
                <th>@lang('modules.leaves.leavePaidStatus')</th>
                <th>@lang('app.department')</th>
                <th>@lang('app.designation')</th>
                <th class="text-right">@lang('app.action')</th>
            </x-slot>

            @forelse($leaveTypes as $key=>$leaveType)
                <tr id="type-{{ $leaveType->id }}">
                    <td>
                        <p class="f-w-500 mb-0"><i class="fa fa-circle mr-1 text-yellow"
                                style="color: {{ $leaveType->color }}"></i>{{ $leaveType->type_name }}
                        </p>
                    </td>
                    <td> {{ ucfirst($leaveType->leavetype) }} </td>
                    <td> {{ $leaveType->no_of_leaves }}</td>
                    <td> {{ ($leaveType->monthly_limit > 0) ? $leaveType->monthly_limit : '--' }}</td>
                    <td>
                        @if ($leaveType->paid == 1)
                            @lang('modules.credit-notes.paid')
                        @else
                            @lang('modules.credit-notes.unpaid')
                        @endif
                    </td>
                    <td>
                        <ol class="pl-3">
                            @foreach ($departments as $department)
                                @if(!is_null($leaveType->department) && in_array($department->id, json_decode($leaveType->department)))
                                    <li>{{$department->team_name}}</li>
                                @endif
                            @endforeach
                        </ol>
                    </td>
                    <td>
                        <ol class="pl-3">
                            @foreach ($designations as $designation)
                                @if(!is_null($leaveType->designation) && in_array($designation->id, json_decode($leaveType->designation)))
                                    <li>{{$designation->name}}</li>
                                @endif
                            @endforeach
                        </ol>
                    </td>
                    <td class="text-right">
                        <div class="task_view">
                            <a href="javascript:;" data-leave-id="{{ $leaveType->id }}"
                                class="editNewLeaveType task_view_more d-flex align-items-center justify-content-center">
                                <i class="fa fa-edit icons mr-2"></i> @lang('app.edit')
                            </a>
                        </div>
                        @if($leaveType->leaves_count > 0)
                        <div class="task_view">
                            <a href="javascript:;" data-leave-id="{{ $leaveType->id }}" data-leave-status="archive"
                                data-toggle="tooltip" data-placement="top" title="@lang('messages.whyArchive')"
                                class="archive-leave task_view_more d-flex align-items-center justify-content-center">
                                <i class="fa fa-archive icons mr-2"></i> @lang('app.archive')
                            </a>
                        </div>
                        @else
                        <div class="task_view mt-1 mt-lg-0 mt-md-0">
                            <a href="javascript:;" data-leave-id="{{ $leaveType->id }}" data-leave-status="force_delete"
                                class="delete-category task_view_more d-flex align-items-center justify-content-center">
                                <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                            </a>
                        </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <x-cards.no-record icon="list" :message="__('messages.noLeaveTypeAdded')" />
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>

</div>
<!-- LEAVE SETTING END -->

<script>
    $('body').on('click', '.delete-category', function() {

    var id = $(this).data('leave-id');
    var force_delete = $(this).data('leave-status');

    Swal.fire({
        title: "@lang('messages.sweetAlertTitle')",
        text: "@lang('messages.deleteLeaveType')",
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

            var url = "{{ route('leaveType.destroy', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                type: 'POST',
                url: url,
                blockUI: true,
                data: {
                    '_token': token,
                    '_method': 'DELETE',
                    'force_delete': force_delete,
                },
                success: function(response) {
                    if (response.status == "success") {
                        $('#type-' + id).fadeOut();
                    }
                }
            });
        }
    });
    });

    // ---

    $('body').on('click', '.archive-leave', function() {

        var id = $(this).data('leave-id');
        var archive = $(this).data('leave-status');

        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.archiveMessageLeave')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmArchive')",
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

                var url = "{{ route('leaveType.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'DELETE',
                        'archive' : archive,
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('#type-' + id).fadeOut();
                        }
                    }
                });
            }
        });
    });


    // add new leave type
    $('#addNewLeaveType').click(function() {
    var url = "{{ route('leaveType.create') }}";
    $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
    $.ajaxModal(MODAL_XL, url);
    });


    $('.editNewLeaveType').click(function() {

        var id = $(this).data('leave-id');

        var url = "{{ route('leaveType.edit', ':id ') }}";
        url = url.replace(':id', id);

        $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_XL, url);
    });

</script>
