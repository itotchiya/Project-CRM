@php
    $addSubTaskPermission = user()->permission('add_sub_tasks');
    $editSubTaskPermission = user()->permission('edit_sub_tasks');
    $deleteSubTaskPermission = user()->permission('delete_sub_tasks');
    $viewSubTaskPermission = user()->permission('view_sub_tasks');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">

    @if ($addSubTaskPermission == 'all'
    || ($addSubTaskPermission == 'added' && $task->added_by == user()->id)
    || ($addSubTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
    || ($addSubTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
    )
        <div class="p-20">

            <div class="row">
                <div class="col-md-12">
                    <a class="f-15 f-w-500" href="javascript:;" id="add-sub-task"><i
                            class="icons icon-plus font-weight-bold mr-1"></i>@lang('app.menu.addSubTask')
                    </a>
                </div>
            </div>

            @php
                $userRoles = user_roles();
                $isAdmin = in_array('admin', $userRoles);
                $isEmployee = in_array('employee', $userRoles);
            @endphp

            @if ($task->approval_send == 1 && $task->project->need_approval_by_admin == 1 && $isEmployee && !$isAdmin && $status->slug == 'waiting_approval')
                <!-- Popup for Send Approval -->
                @include('tasks.ajax.sent-approval-modal')
            @else
                <x-form id="save-subtask-data-form" class="d-none">
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <div class="row">
                        <div class="col-md-12">
                            <x-forms.text :fieldLabel="__('app.title')" fieldName="title" fieldRequired="true"
                                        fieldId="title" :fieldPlaceholder="__('placeholders.task')"/>
                        </div>

                        <div class="col-md-4">
                            <x-forms.datepicker fieldId="sub_task_start_date" :fieldLabel="__('app.startDate')"
                                                fieldName="start_date"
                                                :fieldPlaceholder="__('placeholders.date')"/>
                        </div>

                        <div class="col-md-4">
                            <x-forms.datepicker fieldId="sub_task_due_date" :fieldLabel="__('app.dueDate')"
                                                fieldName="due_date"
                                                :fieldPlaceholder="__('placeholders.date')"/>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group my-3">
                                <x-forms.label fieldId="subTaskAssignee"
                                            :fieldLabel="__('modules.tasks.assignTo')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="user_id"
                                            id="subTaskAssignee" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($task->activeUsers as $item)
                                            <x-user-option :user="$item" :pill="true"/>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.description')"
                                            fieldName="description" fieldId="description" fieldPlaceholder="">
                            </x-forms.textarea>
                        </div>

                        <div class="col-md-12">
                            <a class="f-15 f-w-500" href="javascript:;" id="add-subtask-file"><i
                                    class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')
                            </a>
                        </div>

                        @if ($addSubTaskPermission == 'all' || $addSubTaskPermission == 'added')
                            <div class="col-lg-12 add-file-box d-none">
                                <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                                    :fieldLabel="__('modules.projects.uploadFile')" fieldName="file"
                                                    fieldId="task-file-upload-dropzone"/>
                                <input type="hidden" name="image_url" id="image_url">
                            </div>
                            <div class="col-md-12 add-file-delete-sub-task-filebox d-none mb-5">
                                <div class="w-100 justify-content-end d-flex mt-2">
                                    <x-forms.button-cancel id="cancel-subtaskfile" class="border-0">@lang('app.cancel')
                                    </x-forms.button-cancel>
                                </div>
                            </div>
                            <input type="hidden" name="subTaskID" id="subTaskID">
                            <input type="hidden" name="addedFiles" id="addedFiles">
                        @endif
                        <div class="col-md-12">
                            <div class="w-100 justify-content-end d-flex mt-2">
                                <x-forms.button-cancel id="cancel-subtask" class="border-0 mr-3">@lang('app.cancel')
                                </x-forms.button-cancel>
                                <x-forms.button-primary id="save-subtask" icon="location-arrow">@lang('app.submit')
                                </x-forms.button-primary>
                            </div>
                        </div>
                    </div>
                </x-form>
            @endif
        </div>
    @endif


    @if ($viewSubTaskPermission == 'all' || $viewSubTaskPermission == 'added')
        <div class="d-flex flex-wrap justify-content-between p-20" id="sub-task-list">
            @forelse ($task->subtasks as $subtask)
                <div class="card w-100 rounded-0 border-0 subtask mb-1">

                    <div class="card-horizontal">
                        @php
                            // false means checkbox is clickable
                            $user_id = user()->id;
                            $assigned_to = $subtask->assigned_to;
                            $added_by = $subtask->added_by;

                            $checkBoxDisablePermission =
                                ($editSubTaskPermission === 'both' && ($assigned_to === $user_id || $added_by === $user_id)) ||
                                ($editSubTaskPermission === 'owned' && $assigned_to === $user_id) ||
                                $editSubTaskPermission === 'all' ||
                                ($editSubTaskPermission === 'added' && $added_by === $user_id)
                                ? false : true;

                        @endphp

                        <div class="d-flex">
                            <x-forms.checkbox :fieldId="'checkbox'.$subtask->id" class="task-check"
                                              data-sub-task-id="{{ $subtask->id }}"
                                              :checked="($subtask->status == 'complete') ? true : false" fieldLabel=""
                                              :fieldName="'checkbox'.$subtask->id"
                                              :fieldPermission="$checkBoxDisablePermission" />

                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex">
                                @if ($subtask->assigned_to)
                                    <x-employee-image :user="$subtask->assignedTo"/>
                                @endif

                                <p class="card-title f-14 mr-3 text-dark flex-grow-1" id="subTask">
                                    {!! $subtask->status == 'complete' ? '<s>' . $subtask->title . '</s>' : '<a class="view-subtask text-dark-grey" href="javascript:;" data-row-id=' . $subtask->id . ' >' .  $subtask->title . '</a>' !!}
                                    {!! $subtask->due_date ? '<span class="f-11 text-lightest"><br>'.__('modules.invoices.due') . ': ' . $subtask->due_date->translatedFormat(company()->date_format) . '</span>' : '' !!}
                                </p>
                                <div class="dropdown ml-auto subtask-action">
                                    <button
                                        class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                         aria-labelledby="dropdownMenuLink" tabindex="0">
                                        @if ($viewSubTaskPermission == 'all' || ($viewSubTaskPermission == 'added' && $subtask->added_by == user()->id))
                                            <a class="dropdown-item view-subtask" href="javascript:;"
                                               data-row-id="{{ $subtask->id }}">@lang('app.view')</a>
                                        @endif
                                        @if ($editSubTaskPermission == 'all' || ($editSubTaskPermission == 'added' && $subtask->added_by == user()->id) || ($editSubTaskPermission == 'owned' && $subtask->assigned_to == user()->id) || ($editSubTaskPermission == 'both' && ($subtask->assigned_to == user()->id || $subtask->added_by == user()->id)))
                                            <a class="dropdown-item edit-subtask" href="javascript:;"
                                               data-row-id="{{ $subtask->id }}">@lang('app.edit')</a>
                                        @endif

                                        @if ($deleteSubTaskPermission == 'all' || ($deleteSubTaskPermission == 'added' && $subtask->added_by == user()->id) || ($deleteSubTaskPermission == 'owned' && $subtask->assigned_to == user()->id) || ($deleteSubTaskPermission == 'both' && ($subtask->assigned_to == user()->id || $subtask->added_by == user()->id)))
                                            <a class="dropdown-item delete-subtask" data-row-id="{{ $subtask->id }}"
                                               href="javascript:;">@lang('app.delete')</a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if (count($subtask->files) > 0)
                                <div class="d-flex flex-wrap mt-4">
                                    @foreach ($subtask->files as $file)
                                        <x-file-card :fileName="$file->filename"
                                                     :dateAdded="$file->created_at->diffForHumans()"
                                                     class="subTask{{ $file->id }}">
                                            <x-file-view-thumbnail :file="$file"></x-file-view-thumbnail>

                                            <x-slot name="action">
                                                <div class="dropdown ml-auto file-action">
                                                    <button
                                                        class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                                        type="button" data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="fa fa-ellipsis-h"></i>
                                                    </button>

                                                    <div
                                                        class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                                        aria-labelledby="dropdownMenuLink" tabindex="0">

                                                        @if ($file->icon == 'images')
                                                            <a class="img-lightbox cursor-pointer d-block text-dark-grey f-13 pt-3 px-3" data-image-url="{{ $file->file_url }}" href="javascript:;">@lang('app.view')</a>
                                                        @else
                                                            <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 " target="_blank" href="{{ $file->file_url }}">@lang('app.view')</a>
                                                        @endif

                                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                                           href="{{ route('sub-task-files.download', md5($file->id)) }}">@lang('app.download')</a>

                                                        @if ($deleteSubTaskPermission == 'all' || ($deleteSubTaskPermission == 'added' && $subtask->added_by == user()->id))
                                                            <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-sub-task-file"
                                                               data-row-id="{{ $file->id }}"
                                                               href="javascript:;">@lang('app.delete')</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </x-slot>
                                        </x-file-card>
                                    @endforeach
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @empty
                <x-cards.no-record :message="__('messages.noSubTaskFound')" icon="tasks"/>
            @endforelse

        </div>
    @endif

</div>
<!-- TAB CONTENT END -->

<script>
    $(document).ready(function () {

        var send_approval = "{{ $task->approval_send }}";
        var admin = "{{ in_array('admin', user_roles()) }}";
        var employee = "{{ in_array('employee', user_roles()) }}";
        var needApproval = "{{ $task?->project?->need_approval_by_admin }}";
        var status = "{{ $status->slug }}";

        $('body').on('click', '#add-sub-task', function () {
            if (send_approval == 1 && employee == 1 && admin != 1 && needApproval == 1 && status == 'waiting_approval') {
                $('#send-approval-modal').modal('show');
                $('.modal-backdrop').css('display', 'none');
            }else{
                $(this).closest('.row').addClass('d-none');
                $('#save-subtask-data-form').removeClass('d-none');
            }
        });

        $('.select-picker').selectpicker();

        $('#add-subtask-file').click(function () {
            $('.add-file-box').removeClass('d-none');
            $('#add-subtask-file').addClass('d-none');
        });

        $('#cancel-subtaskfile').click(function () {
            $('.add-file-box').addClass('d-none');
            $('#add-subtask-file').removeClass('d-none');
            return false;
        });

        $('body').on('click', '.view-subtask', function () {
            var id = $(this).data('row-id');
            var url = "{{ route('sub-tasks.show', ':id') }}";
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        var add_sub_task = "{{ $addSubTaskPermission }}";

        if (add_sub_task == "all" || add_sub_task == "added") {

            Dropzone.autoDiscover = false;
            //Dropzone class
            taskDropzone = new Dropzone("div#task-file-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('sub-task-files.store') }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                paramName: "file",
                maxFilesize: DROPZONE_MAX_FILESIZE,
                maxFiles: DROPZONE_MAX_FILES,
                autoProcessQueue: false,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: DROPZONE_MAX_FILES,
                acceptedFiles: DROPZONE_FILE_ALLOW,
                init: function () {
                    taskDropzone = this;
                }
            });
            taskDropzone.on('sending', function (file, xhr, formData) {
                var ids = $('#subTaskID').val();
                formData.append('sub_task_id', ids);
                $.easyBlockUI();
            });
            taskDropzone.on('uploadprogress', function () {
                $.easyBlockUI();
            });
            taskDropzone.on('queuecomplete', function () {
                var msgs = "@lang('messages.recordSaved')";
                window.location.href = "{{ route('tasks.index') }}"
            });
            taskDropzone.on('removedfile', function () {
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).removeClass("has-error");
                $(label).removeClass("is-invalid");
            });
            taskDropzone.on('error', function (file, message) {
                taskDropzone.removeFile(file);
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).find(".help-block").remove();
                var helpBlockContainer = $(grp);

                if (helpBlockContainer.length == 0) {
                    helpBlockContainer = $(grp);
                }

                helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
                $(grp).addClass("has-error");
                $(label).addClass("is-invalid");

            });
        }

        datepicker('#sub_task_start_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#sub_task_due_date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#save-subtask').click(function () {

            const url = "{{ route('sub-tasks.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-subtask-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-subtask",
                data: $('#save-subtask-data-form').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        if (taskDropzone.getQueuedFiles().length > 0) {
                            subTaskID = response.subTaskID;
                            $('#subTaskID').val(response.subTaskID);
                            taskDropzone.processQueue();
                        } else if ($(RIGHT_MODAL).hasClass('in')) {
                            document.getElementById('close-task-detail').click();
                            if ($('#allTasks-table').length) {
                                window.LaravelDataTables["allTasks-table"].draw(true);
                            } else {
                                // window.location.href = response.redirectUrl;
                            }
                        }else {
                            window.location.reload();
                            // window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        $('#cancel-subtask').click(function () {
            $('#save-subtask-data-form').addClass('d-none');
            $('#add-sub-task').closest('.row').removeClass('d-none');
        });

        $('body').on('click', '.delete-sub-task-file', function () {
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
                    var url = "{{ route('sub-task-files.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                $('.subTask' + id).remove();
                            }
                        }
                    });
                }
            });
        });

    });
</script>
