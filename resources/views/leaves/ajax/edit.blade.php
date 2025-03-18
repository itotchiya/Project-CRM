@php
$addLeadAgentPermission = user()->permission('manage_leave_setting');
$approveRejectPermission = user()->permission('approve_or_reject_leaves');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.editLeaves')</h4>
                <div class="row p-20">

                    <div class="col-lg-3 col-md-6">
                        @if (isset($defaultAssign))
                            <x-forms.label class="my-3" fieldId="" :fieldLabel="__('app.name')"
                                fieldRequired="true">
                            </x-forms.label>
                            <input type="hidden" name="user_id" id="user_id" value="{{ $defaultAssign->id }}">
                            <input type="text" value="{{ $defaultAssign->name }}"
                                class="form-control height-35 f-15 readonly-background" readonly>
                        @else
                            <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.messages.chooseMember')"
                                fieldRequired="true">
                            </x-forms.label>
                            <select class="form-control select-picker" name="user_id" id="user_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($employees as $employee)
                                    <x-user-option :user="$employee"
                                                   :selected="(request()->has('default_assign') && request('default_assign') == $employee->id) || ($leave->user_id == $employee->id)">
                                    </x-user-option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.leaves.leaveType')"
                            fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="leave_type_id" id="leave_type_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($leaveQuotas as $leaveQuota)
                                    @if($leaveQuota->leaveType && $leaveQuota->leaveType->leaveTypeCondition($leaveQuota->leaveType, $leaveUser)
                                    && $leaveQuota->leaveType->deleted_at == null
                                    )
                                        <option @selected($leave->leave_type_id == $leaveQuota->leaveType->id) value="{{ $leaveQuota->leaveType->id }}">{{ $leaveQuota->leaveType->type_name }} ({{ $leaveQuota->leaves_remaining }})</option>
                                    @endif
                                @endforeach
                            </select>

                            @if ($addLeadAgentPermission == 'all' || $addLeadAgentPermission == 'added')
                                <x-slot name="append">
                                    <button type="button"
                                        class="btn btn-outline-secondary border-grey add-lead-type"
                                        data-toggle="tooltip" data-original-title="{{ __('modules.leaves.addLeaveType') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    @if ($approveRejectPermission == 'all')
                        <div class="col-lg-3 col-md-6">
                            <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status"
                                search="true">
                                <option @selected($leave->status == 'approved') value="approved">@lang('app.approved')</option>
                                <option @selected($leave->status == 'pending') value="pending">@lang('app.pending')</option>
                                <option @selected($leave->status == 'rejected') value="rejected">@lang('app.rejected')</option>
                            </x-forms.select>
                        </div>
                    @endif

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('app.date')" fieldName="leave_date" fieldId="single_date"
                            :fieldPlaceholder="__('app.date')"
                            :fieldValue="$leave->leave_date->translatedFormat(company()->date_format)" />
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.leaves.reason')"
                                fieldName="reason" fieldId="reason" :fieldPlaceholder="__('placeholders.leave.reason')"
                                :fieldValue="$leave->reason" :fieldRequired="true">
                            </x-forms.textarea>
                        </div>
                    </div>

                    @if ($leave->status == 'rejected')
                        <div class="col-md-12">
                            <div class="form-group my-3">
                                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                    :fieldLabel="__('modules.leaves.rejectReason')" fieldName="reject_reason"
                                    fieldId="reject_reason" fieldPlaceholder="" :fieldValue="$leave->reject_reason">
                                </x-forms.textarea>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-12 mt-3">
                        <a class="f-15 f-w-500" href="javascript:;" id="add-file"><i
                            class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.events.uploadFile')</a>
                    </div>

                    <div class="col-md-12 d-none" id="leave-file">
                        <x-forms.file-multiple class="mr-0"
                        :fieldLabel="__('app.menu.addFile')" fieldName="file"
                        fieldId="file-upload-dropzone" :popover="__('messages.leaveFileMessage')" />

                        <div class="w-100 justify-content-end d-flex mt-2">
                            <button id="cancel-file" type="button"
                                class="btn btn-secondary border-grey rounded f-14">@lang('app.cancel')</button>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div  class="d-flex flex-wrap mt-3" id="leave-file-list">
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
                            <div class="col-md-12" id="no-files">
                                <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file" />
                            </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-leave-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('leaves.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        Dropzone.autoDiscover = false;
        //Dropzone class
        leaveDropzone = new Dropzone("div#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('leave-files.store') }}",
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
            init: function() {
                leaveDropzone = this;
            }
        });
        leaveDropzone.on('sending', function(file, xhr, formData) {
            var ids = "{{ $leave->id }}";
            formData.append('leave_id', ids);
            $.easyBlockUI();
        });
        leaveDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        leaveDropzone.on('queuecomplete', function() {
            window.location.href = "{{ route('leaves.index') }}"
        });
        leaveDropzone.on('removedfile', function () {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        leaveDropzone.on('error', function (file, message) {
            leaveDropzone.removeFile(file);
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

        const dp1 = datepicker('#single_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $leave->leave_date) }}"),
            ...datepickerConfig
        });

        $('#add-file').click(function() {
            $(this).addClass('d-none');
            $('#leave-file').removeClass('d-none');
            $('#no-files').addClass('d-none');
        });

        $('#cancel-file').click(function() {
            $('#leave-file').toggleClass('d-none');
            $('#add-file').toggleClass('d-none');
            $('#no-files').toggleClass('d-none');
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

        $('#save-leave-form').click(function() {
            let markleave = 'no';
            const url = "{{ route('leaves.update', $leave->id) }}";

            function sendeditAjaxRequest(){
                $.easyAjax({
                    url: url,
                    container: '#save-lead-data-form',
                    type: "POST",
                    disableButton: true,
                    blockUI: true,
                    buttonSelector: "#save-leave-form",
                    data: $('#save-lead-data-form').serialize()+ '&markLeave='+markleave,
                    success: function(response) {

                        if (response.status == 'success') {
                                if(leaveDropzone.getQueuedFiles().length > 0) {
                                    leaveDropzone.processQueue();
                                }
                                window.location.href = response.redirectUrl;

                        }

                        if (response.status == 'attendanceMarked') {
                            Swal.fire({
                                title: "@lang('messages.sweetAlertTitle')",
                                text: "@lang('messages.attendanceIsMarked')",
                                icon: 'warning',
                                showCancelButton: true,
                                focusConfirm: false,
                                confirmButtonText: "@lang('app.apply')",
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
                                    markleave = 'yes';
                                    sendeditAjaxRequest();
                                }
                            });
                        }
                    }
                });
            }

            sendeditAjaxRequest();
        });

        $('body').on('click', '.add-lead-type', function() {
            var url = "{{ route('leaveType.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#user_id').change(function() {
            var id = $(this).val();
            if (id == '') {
                id = 0;
            }
            var url = "{{ route('employee-leaves.employee_leave_types', ':id') }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-lead-data-form',
                blockUI: true,
                success: function(data) {
                    $('#leave_type_id').html(data.data);
                    $('#leave_type_id').selectpicker('refresh');
                }
            })
        });

        init(RIGHT_MODAL);
    });
</script>
