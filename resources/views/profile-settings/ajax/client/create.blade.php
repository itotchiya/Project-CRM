
@php
$viewDocumentPermission = user()->permission('view_client_document');
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.menu.addFile')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="save-client-file-data-form">
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <div class="row">
            <div class="col-md-12">
                <x-forms.text :fieldLabel="__('modules.projects.fileName')" fieldName="name"
                    fieldRequired="true" fieldId="file_name" />
            </div>
            <div class="col-md-12">
                <x-forms.file :fieldLabel="__('modules.projects.uploadFile')" fieldName="file"
                    fieldRequired="true" fieldId="client_file" allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg" :popover="__('messages.fileFormat.multipleImageFile')" />
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="submit-document" icon="check">@lang('app.submit')
    </x-forms.button-primary>
</div>

<script>
    $('#submit-document').click(function() {
        var url = "{{ route('client-docs.store') }}";

        $.easyAjax({
            url: url,
            container: '#save-client-file-data-form',
            type: "POST",
            disableButton: true,
            buttonSelector: "#submit-document",
            file: true,
            data: $('#editSettings').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    $(MODAL_DEFAULT).modal('hide');
                    $('#task-file-list').html(response.view);
                }
            }
        })
    });

    init('#save-document-data-form');
</script>
