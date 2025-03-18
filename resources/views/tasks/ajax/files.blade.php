@php
    $addTaskFilePermission = user()->permission('add_task_files');
    $viewTaskFilePermission = user()->permission('view_task_files');
    $deleteTaskFilePermission = user()->permission('delete_task_files');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<style>
    .file-action {
        visibility: hidden;
    }

    .file-card:hover .file-action {
        visibility: visible;
    }

</style>

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    @if ($addTaskFilePermission == 'all'
    || ($addTaskFilePermission == 'added' && $task->added_by == user()->id)
    || ($addTaskFilePermission == 'owned' && in_array(user()->id, $taskUsers))
    || ($addTaskFilePermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
    )
        <div class="p-20">

            <div class="row">
                <div class="col-md-12">
                    <a class="f-15 f-w-500" href="javascript:;" id="add-task-file"><i
                            class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')</a>
                </div>
            </div>

            @php
                $userRoles = user_roles();
                $isAdmin = in_array('admin', $userRoles);
                $isEmployee = in_array('employee', $userRoles);
            @endphp

            @if ($task->approval_send == 1 && $task?->project?->need_approval_by_admin == 1 && $isEmployee && !$isAdmin && $status->slug == 'waiting_approval')
                <!-- Popup for Send Approval -->
                @include('tasks.ajax.sent-approval-modal')
            @else
                <x-form id="save-taskfile-data-form" class="d-none">

                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <div class="row">
                        <div class="col-md-12 d-none error-block">
                            <x-alert type="danger" id="error"></x-alert>
                        </div>
                        <div class="col-md-12">
                            <x-forms.file-multiple fieldLabel="" fieldName="file[]" fieldId="task-file-upload-dropzone"/>
                        </div>
                        <div class="col-md-12">
                            <div class="w-100 justify-content-end d-flex mt-2">
                                <x-forms.button-cancel id="cancel-taskfile" class="border-0">@lang('app.cancel')
                                </x-forms.button-cancel>
                            </div>
                        </div>
                    </div>
                </x-form>
            @endif
        </div>
    @endif

    <div class="d-flex flex-wrap p-20" id="task-file-list">
        @php
            $filesShowCount = 0; // This is done because if fies uploaded and not have permission to view then no record found message should be shown
        @endphp
        @forelse($task->files as $file)
            @if ($viewTaskFilePermission == 'all' || ($viewTaskFilePermission == 'added' && $file->added_by == user()->id))
                @php
                    $filesShowCount++;
                @endphp
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
                                    @if ($file->icon = 'images')
                                        @if ($file->icon == 'images')
                                            <a class="img-lightbox cursor-pointer d-block text-dark-grey f-13 pt-3 px-3" data-image-url="{{ $file->file_url }}" href="javascript:;">@lang('app.view')</a>
                                        @else
                                            <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 " target="_blank" href="{{ $file->file_url }}">@lang('app.view')</a>
                                        @endif
                                    @endif
                                    <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                    href="{{ route('task_files.download', md5($file->id)) }}">@lang('app.download')</a>

                                    @if ($deleteTaskFilePermission == 'all' || ($deleteTaskFilePermission == 'added' && $file->added_by == user()->id))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-file"
                                        data-row-id="{{ $file->id }}" href="javascript:;">@lang('app.delete')</a>
                                    @endif
                                </div>
                            </div>
                        </x-slot>

                </x-file-card>
            @endif
        @empty
            <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file"/>
        @endforelse

        @if ($filesShowCount == 0 && $task->files->count() > 0)
            <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file"/>
        @endif
    </div>

</div>
<!-- TAB CONTENT END -->

<script>
    $(document).ready(function () {
        var add_task_files = "{{ $addTaskFilePermission }}";
        var send_approval = "{{ $task->approval_send }}";
        var admin = "{{ in_array('admin', user_roles()) }}";
        var employee = "{{ in_array('employee', user_roles()) }}";
        var needApproval = "{{ $task?->project?->need_approval_by_admin }}";
        var status = "{{ $status->slug }}";

        $('#add-task-file').click(function () {
            if (send_approval == 1 && employee == 1 && admin != 1 && needApproval == 1 && status == 'waiting_approval') {
                $('#send-approval-modal').modal('show');
                $('.modal-backdrop').css('display', 'none');
            }else{
                $(this).closest('.row').addClass('d-none');
                $('.error-block').addClass('d-none');
                $('#save-taskfile-data-form').removeClass('d-none');
            }
        });


        if (add_task_files == "all" || add_task_files == "added") {

            Dropzone.autoDiscover = false;
            taskDropzone = new Dropzone("div#task-file-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('task-files.store') }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                paramName: "file",
                maxFilesize: DROPZONE_MAX_FILESIZE,
                maxFiles: DROPZONE_MAX_FILES,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: DROPZONE_MAX_FILES,
                acceptedFiles: DROPZONE_FILE_ALLOW,
                init: function () {
                    taskDropzone = this;
                }
            });
            taskDropzone.on('sending', function (file, xhr, formData) {
                var ids = "{{ $task->id }}";
                formData.append('task_id', ids);
                $.easyBlockUI();
            });
            taskDropzone.on('uploadprogress', function () {
                $.easyBlockUI();
            });
            taskDropzone.on('completemultiple', function (file) {
                var response = JSON.parse(file[0].xhr.response);

                if (response?.error?.message) {
                    $('.error-block').removeClass('d-none');
                    $('#error').html(response?.error?.message);
                }

                var taskView = response.view;
                taskDropzone.removeAllFiles();
                $.easyUnblockUI();

                $('#task-file-list').html(taskView);
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

        $('#cancel-taskfile').click(function () {
            $('#save-taskfile-data-form').addClass('d-none');
            $('#add-task-file').closest('.row').removeClass('d-none');
            return false;
        });
    });
</script>
