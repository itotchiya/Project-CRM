<div class="modal-header">
    <h5 class="modal-title">@lang('modules.projectCategory.addProjectCategory')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<x-form id="createProjectCategory" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <div class="col-sm-12">
                <x-forms.text fieldId="category_name" :fieldLabel="__('modules.projectCategory.categoryName')"
                    fieldName="category_name" fieldRequired="true" :fieldPlaceholder="__('placeholders.category')">
                </x-forms.text>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-project-category" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>


<script>

    $('#save-project-category').click(function () {
        $.easyAjax({
            container: '#createProjectCategory',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-project-category",
            url: "{{ route('project-settings.saveProjectCategory') }}",
            data: $('#createProjectCategory').serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }
        })
    });
</script>
