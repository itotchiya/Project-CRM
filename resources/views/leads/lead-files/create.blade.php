<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.lead.addFile')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="col-lg-12">
        <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.menu.addFile')"
            fieldName="file" fieldId="file-upload-dropzone" :fieldRequired="true" />
        <input type="hidden" name="image_url" id="image_url">
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-files" disabled icon="check">@lang('app.save')</x-forms.button-primary>
</div>


<script>
    $(document).ready(function() {

        Dropzone.autoDiscover = false;
        //Dropzone class
        leadDropzone = new Dropzone("div#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('deal-files.store') }}",
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
                leadDropzone = this;
            }
        });
        leadDropzone.on('sending', function(file, xhr, formData) {
            formData.append('lead_id', $('#add-files').data('lead-id'));
            $.easyBlockUI();
        });
        leadDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        leadDropzone.on('queuecomplete', function() {
            var msgs = "@lang('messages.recordSaved')";
            leadFilesView(fileLayout);
            $(MODAL_LG).modal('hide');
        });
        leadDropzone.on('removedfile', function () {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        leadDropzone.on('error', function (file, message) {
            leadDropzone.removeFile(file);
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
        leadDropzone.on('addedfile', function() {
            $('#save-files').prop("disabled", false);
        });
    });

    $('#save-files').click(function() {
        leadDropzone.processQueue();
    });

</script>
