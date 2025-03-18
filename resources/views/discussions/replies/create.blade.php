<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<div class="modal-header">
    <h5 class="modal-title">@lang('app.menu.addReply')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="createMethods" method="POST" class="ajax-form">
            <input type="hidden" name="discussion_id" value="{{ $discussionId }}">

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group my-3">
                        <x-forms.label fieldReuired="true" fieldId="description" :fieldLabel="__('app.reply')">
                        </x-forms.label>
                        <div id="description"></div>
                        <textarea name="description" id="description-text" class="d-none"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                        :fieldLabel="__('app.menu.addFile')" fieldName="file"
                        fieldId="discussion-file-upload-dropzone" />
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-discussion1" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(document).ready(function () {
        quillImageLoad('#description');
        let discussion_reply_id;
        /* Upload images */
        Dropzone.autoDiscover = false;
        //Dropzone class
        taskDropzone = new Dropzone("#discussion-file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('discussion-files.store') }}",
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
            formData.append('discussion_id', {{$discussionId}});
            formData.append('discussion_reply_id', discussion_reply_id);
            $.easyBlockUI();
        });
        taskDropzone.on('uploadprogress', function () {
            $.easyBlockUI();
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
        taskDropzone.on('completemultiple', function () {
            $.easyAjax({
                url: '{{ route('discussion-reply.get_replies', $discussionId) }}',
                type: 'GET',
                success: function (response) {
                    if(response.status == 'success'){
                        $('#right-modal-content').html(response.html);
                        $(MODAL_XL).modal('hide');
                        $.easyUnblockUI();
                    }
                }
            });
        });

        // save discussion
        $('#save-discussion1').click(function() {
            const note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

            $.easyAjax({
                url: "{{ route('discussion-reply.store') }}",
                container: '#createMethods',
                type: "POST",
                blockUI: true,
                data: $('#createMethods').serialize(),
                success: function(response) {
                    if (response.status == "success") {
                        if (taskDropzone.getQueuedFiles().length > 0) {
                            discussion_reply_id = response.discussion_reply_id;
                            taskDropzone.processQueue();
                        } else {
                            $('#right-modal-content').html(response.html);
                            $(MODAL_XL).modal('hide');
                        }
                    }
                }
            })
        });

        init('#createMethods');
    });


</script>
