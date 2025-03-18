<style>
    .select-action-type {
        position: relative;
    }
</style>

@php
$addTaskPermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('add_tasks');
$viewUnassignedTasksPermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('view_unassigned_tasks');
$projectArchived = $project->trashed();
@endphp

<!-- ROW START -->
<div class="row py-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        @if ($projectArchived)
            <x-alert type="info" icon="info-circle">@lang('messages.archivedTaskNotWork')</x-alert>
        @endif

        @if ($viewUnassignedTasksPermission == 'all' && $unAssignedTask > 0)
         @php
            $has = ($unAssignedTask == 1) ? 'has' : 'have';
            $taskName = ($unAssignedTask == 1) ? 'task' : 'tasks';
         @endphp
        <x-alert type="warning" icon="info-circle">@lang('messages.unassignedTask', ['unassigned' => $unAssignedTask, 'task' => $taskName, 'has' => $has]) <a class="unassigned-task" id="unassigned-task" href="javascript:;">@lang('app.clickHere')</a></x-alert>
        @endif

        <div class="d-flex" id="table-actions">
            @if (($addTaskPermission == 'all' || $addTaskPermission == 'added' || $project->project_admin == user()->id) && !$projectArchived)
                <x-forms.link-primary :link="route('tasks.create').'?task_project_id='.$project->id"
                    class="mr-3 openRightModal" icon="plus" data-redirect-url="{{ url()->full() }}">
                    @lang('app.addTask')
                </x-forms.link-primary>
            @endif
        </div>
        <!-- Add Task Export Buttons End -->


        <div class="d-flex justify-content-between">
            <form action="" class="flex-grow-1 " id="filter-form">
                <div class="d-flex mt-3">
                    <!-- STATUS START -->
                    <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('app.status')" fieldId="status" />
                        <select class="form-control select-picker" name="status" id="status">
                            <option value="not finished">@lang('modules.tasks.hideCompletedTask')</option>
                            <option value="all">@lang('app.all')</option>
                            @foreach ($taskBoardStatus as $status)
                                <option value="{{ $status->id }}">{{ $status->slug == 'completed' || $status->slug == 'incomplete' ? __('app.' . $status->slug) : $status->column_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- STATUS END -->

                    <!-- STATUS START -->
                    <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('modules.tasks.assignTo')" fieldId="assignedTo" />
                        <select class="form-control select-picker" id="assignedTo" data-live-search="true"
                        data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($project->projectMembers as $employee)
                                <x-user-option :user="$employee"></x-user-option>
                            @endforeach
                            @if ($viewUnassignedTasksPermission == 'all')
                                <option value="unassigned">@lang('modules.tasks.unassigned')</option>
                            @endif
                        </select>
                    </div>
                    <!-- STATUS END -->

                    <!-- STATUS START -->
                    <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('modules.projects.milestones')" fieldId="milestone_id" />
                        <select class="form-control select-picker" id="milestone_id" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($project->milestones as $milestone)
                                <option value="{{ $milestone->id }}">{{ $milestone->milestone_title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- STATUS END -->


                    <!-- SEARCH BY TASK START -->
                    <div class="select-box py-2 px-lg-2 px-md-2 px-0 mr-3">
                        <x-forms.label fieldId="status" />
                        <div class="input-group bg-grey rounded">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-additional-grey">
                                    <i class="fa fa-search f-13 text-dark-grey"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control f-14 p-1 height-35 border" id="search-text-field"
                                placeholder="@lang('app.startTyping')">
                        </div>
                    </div>
                    <!-- SEARCH BY TASK END -->

                    <!-- RESET START -->
                    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 mt-4">
                        <x-forms.button-secondary class="btn-xs d-none height-35 mt-2" id="reset-filters"
                            icon="times-circle">
                            @lang('app.clearFilters')
                        </x-forms.button-secondary>
                    </div>
                    <!-- RESET END -->
                </div>
            </form>

            <x-datatable.actions class="mt-5">
                <div class="select-status mr-3 pl-3" data-need-approval="{{ $project->need_approval_by_admin }}">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                        <option value="milestone">@lang('app.milestone')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        @foreach ($taskBoardStatus as $status)
                            <option value="{{ $status->id }}">{{ $status->slug == 'completed' || $status->slug == 'incomplete' ? __('app.' . $status->slug) : $status->column_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="select-milestone mr-3 d-none quick-action-field2 change-milestone-action" id="change-milestone-action">
                    <select name="milestone" class="form-control select-picker">
                        @foreach ($project->milestones as $milestone)
                            @if ($milestone->status == 'incomplete')
                                <option value="{{ $milestone->id }}">{{ $milestone->milestone_title }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </x-datatable.actions>
        </div>


        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->

    </div>

</div>
<!-- ROW END -->
@include('sections.datatable_js')

<script>
    $('#allTasks-table').on('preXhr.dt', function(e, settings, data) {

        var projectID = "{{ $project->id }}";
        var status = $('#status').val();
        var searchText = $('#search-text-field').val();
        var assignedTo = $('#assignedTo').val();
        var trashedData = "{{ $project->trashed() == 1 ? 'true' : 'false' }}";
        var milestone_id = $('#milestone_id').val();
        data['projectId'] = projectID;
        data['status'] = status;
        data['assignedTo'] = assignedTo;
        data['searchText'] = searchText;
        data['trashedData'] = trashedData;
        data['milestone_id'] = milestone_id;
        data['project_admin'] = "{{ ($project->project_admin == user()->id) ? 1 : 0 }}";
    });
    const showTable = () => {
        window.LaravelDataTables["allTasks-table"].draw(true);
    }

    $('#status, #assignedTo, #milestone_id')
        .on('change keyup',
            function() {
                if ($('#status').val() != "not finished") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#assignedTo').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else if ($('#milestone_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                }else {
                    $('#reset-filters').addClass('d-none');
                    showTable();
                }
            });

    $('#search-text-field').on('keyup', function() {
        if ($('#search-text-field').val() != "") {
            $('#reset-filters').removeClass('d-none');
            showTable();
        }
    });

    $('#reset-filters,#reset-filters-2').click(function() {
        $('#filter-form')[0].reset();
        $('#filter-form #status').val('not finished');
        $('#filter-form .select-picker').selectpicker("refresh");
        $('#reset-filters').addClass('d-none');
        showTable();
    });

    $('.unassigned-task').click(function() {
        $('#assignedTo').val('unassigned');
        $('#assignedTo').selectpicker("refresh");
        $('#reset-filters').removeClass('d-none');
        showTable();
    });


    $('body').on('click', '#pinnedTaskItem', function () {
            var type = $(this).data('pinned');
                var id = $(this).data('task-id');
                var pinType = 'task';

                var dataPin = type.trim(type);
                if (dataPin == 'pinned') {
                    Swal.fire({
                        title: "@lang('messages.sweetAlertTitle')",
                        icon: 'warning',
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: "@lang('messages.confirmUnpin')",
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
                            var url = "{{ route('tasks.destroy_pin', ':id') }}";
                            url = url.replace(':id', id);

                            var token = "{{ csrf_token() }}";
                            $.easyAjax({
                                type: 'POST',
                                url: url,
                                data: {
                                    '_token': token,
                                    'type': pinType
                                },
                                success: function (response) {
                                    if (response.status == "success") {
                                        window.location.reload();
                                    }
                                }
                            })
                        }
                    });

                } else {
                    Swal.fire({
                        title: "@lang('messages.sweetAlertTitle')",
                        icon: 'warning',
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: "@lang('messages.confirmPin')",
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
                            var url = "{{ route('tasks.store_pin') }}?type=" + pinType;

                            var token = "{{ csrf_token() }}";
                            $.easyAjax({
                                type: 'POST',
                                url: url,
                                data: {
                                    '_token': token,
                                    'task_id': id
                                },
                                success: function (response) {
                                    if (response.status == "success") {
                                        window.location.reload();
                                    }
                                }
                            });
                        }
                    });
                }
            });

    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('user-id');
        let activeRunning = $(this).data('active-running');

        if (activeRunning == 1) {

            Swal.fire({
                title: "@lang('messages.taskTimerRunning')",
                text: "@lang('messages.stopTheTimer')",
                icon: 'warning',
                showConfirmButton: true,
                confirmButtonText: "@lang('messages.timerOkay')",
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {

                }
            });
        } else {
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
                    var url = "{{ route('tasks.destroy', ':id') }}";
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
                                if($('#unassigned-task').length) {
                                    window.location.reload() = "{{ route('projects.show', $project->id)}}" + "?tab=tasks";
                                }
                                showTable();
                            }
                        }
                    });
                }
            });
        }
    });

    $('#allTasks-table').on('change', '.change-status', function() {
        var url = "{{ route('tasks.change_status') }}";
        var token = "{{ csrf_token() }}";
        var id = $(this).data('task-id');
        var status = $(this).val();
        var needApproval = $(this).data('need-approval');

        var rolesJson = `{!! addslashes(json_encode(user()->roles)) !!}`; // Fetch roles JSON and escape special characters
        var roles = JSON.parse(rolesJson); // Parse JSON string to JavaScript object

        function isAdmin() {
            for (var i = 0; i < roles.length; i++) {
                if (roles[i].name === 'admin') {
                    return true;
                }
            }
        }

        if (id != "" && status != "") {
            if(status == 'completed' && !isAdmin() && needApproval == 1){
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.approvalmsgsent')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('app.yes')",
                    cancelButtonText: "@lang('app.no')",
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
                        var url = "{{ route('tasks.send_approval', ':id') }}";
                        url = url.replace(':id', id);

                        var token = "{{ csrf_token() }}";
                        var isApproval = 1;
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                taskId: id,
                                isApproval: isApproval,
                                '_method': 'POST'
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    showTable();
                                }
                            }
                        });
                    }
                });
            }else{
                $.easyAjax({
                    url: url,
                    type: "POST",
                    data: {
                        '_token': token,
                        taskId: id,
                        status: status,
                        sortBy: 'id'
                    },
                    success: function(data) {
                        window.LaravelDataTables["allTasks-table"].draw(true);
                    }
                });
            }
        }
    });

    $('#allTasks-table').on('change', '.change-milestone-action', function() {

        var url = "{{ route('tasks.change_milestone') }}";
        var token = "{{ csrf_token() }}";
        var id = $(this).data('task-id');
        var milestoneId = $(this).val();
        $.easyAjax({
            url: url,
            type: "POST",
            data: {
                '_token': token,
                taskId: id,
                milestone_id: milestoneId
            },
            success: function(data) {
                window.LaravelDataTables["allTasks-table"].draw(true);
            }
        });
    });

    $('#quick-action-type').change(function() {
        const actionValue = $(this).val();
        if (actionValue != '') {
            $('#quick-action-apply').removeAttr('disabled');

            if (actionValue == 'change-status') {
                $('.quick-action-field').addClass('d-none');
                $('#change-status-action').removeClass('d-none');
            } else {
                $('.quick-action-field').addClass('d-none');
            }

            if (actionValue == 'milestone') {
                $('.quick-action-field2').addClass('d-none');
                $('#change-milestone-action').removeClass('d-none');
            } else {
                $('.quick-action-field2').addClass('d-none');
            }
        } else {
            $('#quick-action-apply').attr('disabled', true);
            $('.quick-action-field').addClass('d-none');
            $('.quick-action-field2').addClass('d-none');
        }
    });

    $('#quick-action-apply').click(function() {
        const actionValue = $('#quick-action-type').val();
        if (actionValue == 'delete') {
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
                    applyQuickAction();
                }
            });

        } else {
            applyQuickAction();
        }
    });

    const applyQuickAction = () => {
        var rowdIds = $("#allTasks-table input:checkbox:checked").map(function() {
            return $(this).val();
        }).get();

        var url = "{{ route('tasks.apply_quick_action') }}?row_ids=" + rowdIds;

        $.easyAjax({
            url: url,
            container: '#quick-action-form',
            type: "POST",
            disableButton: true,
            buttonSelector: "#quick-action-apply",
            data: $('#quick-action-form').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    showTable();
                    resetActionButtons();
                    deSelectAll();
                    $('#quick-action-form').hide();
                }
            }
        })
    };

    $('#allTasks-table').on('click', '.start-timer', function() {
        var url = "{{ route('timelogs.start_timer') }}";
        var user_id = "{{ user()->id }}";
        var token = "{{ csrf_token() }}";
        var task_id = $(this).data('task-id');
        var memo = "{{ __('app.task') }}#" + $(this).data('task-id');

        $.easyAjax({
            url: url,
            container: '#allTasks-table',
            type: "POST",
            blockUI: true,
            data: {
                task_id: task_id,
                memo: memo,
                '_token': token,
                user_id: user_id
            },
            success: function(response) {
                if (response.status == 'success') {
                    if (response.activeTimerCount > 0) {
                        $('#show-active-timer .active-timer-count').html(response.activeTimerCount);
                    } else {
                        $('#show-active-timer .active-timer-count').addClass('d-none');
                    }

                    $('#timer-clock').html(response.clockHtml);
                    if ($('#allTasks-table').length) {
                        window.LaravelDataTables["allTasks-table"].draw(true);
                    }
                }
            }
        })
    });


    $('#allTasks-table').on('click', '.resume-timer', function() {
        var id = $(this).data('time-id');
        var url = "{{ route('timelogs.resume_timer', ':id') }}";
        url = url.replace(':id', id);
        var token = '{{ csrf_token() }}';
        $.easyAjax({
            url: url,
            blockUI: true,
            type: "POST",
            data: {
                timeId: id,
                _token: token
            },
            success: function(response) {
                if (response.status == 'success') {
                    if (response.activeTimerCount > 0) {
                        $('#show-active-timer .active-timer-count').html(response.activeTimerCount);
                    } else {
                        $('#show-active-timer .active-timer-count').addClass('d-none');
                    }

                    $('#timer-clock').html(response.clockHtml);
                    if ($('#allTasks-table').length) {
                        window.LaravelDataTables["allTasks-table"].draw(true);
                    }
                }
            }
        })
    });

    $('#allTasks-table').on('click', '.pause-timer', function() {
        var id = $(this).data('time-id');
        var url = "{{ route('timelogs.pause_timer', ':id') }}";
        url = url.replace(':id', id);
        var token = '{{ csrf_token() }}';
        $.easyAjax({
            url: url,
            blockUI: true,
            type: "POST",
            disableButton: true,
            buttonSelector: "#pause-timer-btn",
            data: {
                timeId: id,
                _token: token
            },
            success: function(response) {
                if (response.status == 'success') {
                    if (response.activeTimerCount > 0) {
                        $('#show-active-timer .active-timer-count').html(response.activeTimerCount);
                    } else {
                        $('#show-active-timer .active-timer-count').addClass('d-none');
                    }

                    $('#timer-clock').html(response.clockHtml);
                    if ($('#allTasks-table').length) {
                        window.LaravelDataTables["allTasks-table"].draw(true);
                    }
                }
            }
        })
    });

    $('#allTasks-table').on('click', '.stop-timer', function() {
            var url = "{{ route('timelogs.stopper_alert', ':id') }}?via=timelog";
            var id = $(this).data('time-id');
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })
</script>
