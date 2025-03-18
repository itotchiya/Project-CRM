<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-notice-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.notices.updateNotice')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">

                            <div class="col-lg-4 col-md-6">
                                <div class="form-group my-3">
                                    <label class="f-14 text-dark-grey mb-12 w-100" for="usr">Notify</label>
                                    <div class="d-flex">
                                        <x-forms.radio fieldId="toEmployee"
                                            :fieldLabel="__('modules.notices.toEmployee')" fieldName="to"
                                            fieldValue="employee" :checked="$notice->to == 'employee'">
                                        </x-forms.radio>
                                        @if (in_array('clients', user_modules()) || $notice->to == 'client')
                                            <x-forms.radio fieldId="toClient" :fieldLabel="__('modules.notices.toClients')"
                                                fieldValue="client" fieldName="to" :checked="$notice->to == 'client'">
                                            </x-forms.radio>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.text fieldId="heading" :fieldLabel="__('modules.notices.noticeHeading')"
                                    fieldName="heading" fieldRequired="true" :fieldPlaceholder="__('placeholders.name')"
                                    :fieldValue="$notice->heading">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-4 col-md-6 department @if ($notice->to == 'client' || !in_array('clients', user_modules())) d-none @endif">
                                <x-forms.select fieldId="team_id" :fieldLabel="__('app.department')" fieldName="team_id"
                                    search="true">
                                    <option value="0"> -- </option>
                                    @foreach ($teams as $team)
                                        <option
                                            {{ isset($notice->department_id) && $notice->department_id == $team->id ? 'selected' : '' }}
                                            value="{{ $team->id }}">{{ $team->team_name }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>

                            <div class="col-lg-6 col-md-6  @if($notice->to == 'client') d-none @endif" id="employeesDiv">
                                <x-forms.label class="my-3" fieldId="selectEmployee"
                                    :fieldLabel="__('app.select') . ' ' . __('app.employee')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control multiple-users select-picker" multiple name="employees[]"
                                        id="selectEmployee" data-live-search="true" data-size="8">
                                        @foreach ($employees as $item)
                                            <x-user-option :user="$item" :pill="true" :selected="in_array($item->id, $employeeArray)"/>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>

                            <div class="col-lg-6 col-md-6 @if($notice->to == 'employee') d-none @endif" id="clientsDiv">
                                <x-forms.label class="my-3" fieldId="selectClient"
                                    :fieldLabel="__('app.select') . ' ' . __('app.client')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control multiple-users select-picker" multiple name="clients[]"
                                        id="selectClient" data-live-search="true" data-size="8">
                                        @foreach ($clients as $item)
                                            <x-user-option :user="$item" :pill="true" :selected="in_array($item->id, $clientArray)"/>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group my-3">
                                    <x-forms.label class="my-3" fieldId="description-text"
                                        :fieldLabel="__('modules.notices.noticeDetails')">
                                    </x-forms.label>
                                    <div id="description"> {!! $notice->description !!} </div>
                                    <textarea name="description" id="description-text" class="d-none"></textarea>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                    :fieldLabel="__('app.menu.addFile')" fieldName="file"
                                        fieldId="file-upload-dropzone"/>
                                <input type="hidden" name="image_url" id="image_url">
                            </div>
                            <input type="hidden" name="noticeID" id="noticeID">

                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-notice" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('notices.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>

<script>
    $(document).ready(function() {

        quillMention(null, '#description');

        let checkSize = false;

        Dropzone.autoDiscover = false;
        //Dropzone class
        noticeDropzone = new Dropzone("div#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('notice-files.store') }}",
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
                noticeDropzone = this;
            }
        });
        noticeDropzone.on('sending', function (file, xhr, formData) {
            checkSize = false;
            var ids = $('#noticeID').val();
            formData.append('notice_id', ids);
            $.easyBlockUI();
        });
        noticeDropzone.on('uploadprogress', function () {
            $.easyBlockUI();
        });
        noticeDropzone.on('queuecomplete', function () {
            if (checkSize == false) {
                window.location.href = localStorage.getItem("redirect_notice");
            }
        });
        noticeDropzone.on('removedfile', function () {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        noticeDropzone.on('error', function (file, message) {
            noticeDropzone.removeFile(file);
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

            checkSize = true;
        });

        // show/hide project detail
        $(document).on('change', 'input[type=radio][name=to]', function() {
            $('.department').toggleClass('d-none');
        });

        $(".select-picker").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $('input[type=radio][name=to]').change(function() {
            let type = $(this).val();

            if (type == 'employee') {
                $('#clientsDiv').addClass('d-none');
                $('#employeesDiv').removeClass('d-none');
            }
            else {
                $('#employeesDiv').addClass('d-none');
                $('#clientsDiv').removeClass('d-none');
            }
        });

        $('body').on('change', '#team_id', function () {
            const id = $(this).val();
            if (id !== undefined && id !== '') {
                var url = "{{ route('employees.by_department', ':id') }}";
                url = url.replace(':id', id);

                $.easyAjax({
                    url: url,
                    type: "GET",
                    blockUI: true,
                    data: {id: id},
                    success: function (response) {
                        if (response.status == "success") {
                            $.unblockUI();
                            $('#selectEmployee').html(response.data);
                            $('#selectEmployee').selectpicker('refresh');
                        }
                    }
                })
            }
        });

        $('#save-notice').click(function() {
            const url = "{{ route('notices.update', [$notice->id]) }}";

            var note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

            $.easyAjax({
                url: url,
                container: '#save-notice-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-notice",
                file: true,
                data: $('#save-notice-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if (typeof noticeDropzone !== 'undefined' && noticeDropzone.getQueuedFiles().length > 0) {
                            noticeID = response.noticeID;
                            $('#noticeID').val(response.noticeID);
                            (response.add_more == true) ? localStorage.setItem("redirect_notice", window.location.href) : localStorage.setItem("redirect_notice", response.redirectUrl);
                            noticeDropzone.processQueue();
                        }
                        else if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
