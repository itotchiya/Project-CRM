<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.invoices.unitType')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createUnitType">
        <div class="row border-top-grey ">
            <div class="col-sm-12">
                <x-forms.text fieldId="unit_type" :fieldLabel="__('modules.invoices.unitType')"
                    fieldName="unit_type" fieldRequired="true" :fieldPlaceholder="__('placeholders.category')" fieldValue="{{ $unitType->unit_type}}">
                </x-forms.text>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-unit" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('#save-unit').click(function() {
        var url = "{{ route('unit-type.update', $unitType->id) }}";
        $.easyAjax({
            url: url,
            container: '#createUnitType',
            type: "PUT",
            data: $('#createUnitType').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });

    $('[contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
        let rowId = $(this).data('row-id');
    }).blur(function() {
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let value = $(this).html();

            var url = "{{ route('unit-type.update', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";
            $.easyAjax({
                url: url,
                container: '#row-' + id,
                type: "POST",
                data: {
                    'unit_type': value,
                    '_token': token,
                    '_method': 'PUT'
                },
                blockUI: true,
                success: function(response) {
                    if (response.status == 'success') {
                        $('#unit_type_id').html(response.data);
                        $('#unit_type_id').selectpicker('refresh');
                    }
                }
            })
        }
    });

</script>

