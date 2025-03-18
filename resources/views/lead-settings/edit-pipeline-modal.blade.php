<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />

<style>
    #colorpicker .form-group {
        width: 87%;
    }
</style>


<x-form id="editStatus" method="PUT" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('app.edit') @lang('modules.deal.pipeline')</h5>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="portlet-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-sm-4 col-md-12 col-lg-6">
                            <x-forms.text fieldId="type" :fieldLabel="__('app.name')"
                                fieldName="name" fieldRequired="true" :fieldPlaceholder="__('placeholders.status')" :fieldValue="$pipeline->name">
                            </x-forms.text>
                        </div>
                        <div class="col-sm-4 col-md-12 col-lg-6">
                            <div id="colorpicker" class="input-group">

                                <div class="form-group my-3 text-left">
                                    <x-forms.label fieldId="colorselector" :fieldLabel="__('modules.tasks.labelColor')"
                                        fieldRequired="true">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <input type="text" name="label_color" id="colorselector" value="{{ $pipeline->label_color }}"
                                            class="form-control height-35 f-15 light_text">
                                        <x-slot name="append">
                                            <span class="input-group-text colorpicker-input-addon height-35"><i></i></span>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
        </div>
    </div>

    <div class="modal-footer">

        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
        @if(!$pipeline->default)
            <button type="button" class="btn-danger rounded f-14 p-2 delete-pipeline">
                    <i class="fa fa-trash mr-3"></i>
                    @lang('app.delete')
            </button>
        @endif


        <x-forms.button-primary id="save-status" icon="check">@lang('app.save')</x-forms.button-primary>

    </div>
</x-form>

<script>

    $('#colorpicker').colorpicker({"color": "{{ $pipeline->label_color }}"});

    $(".select-picker").selectpicker();

    // save status
    $('#save-status').click(function() {
        $.easyAjax({
            url: "{{route('lead-pipeline-setting.update', $pipeline->id)}}",
            container: '#editStatus',
            type: "POST",
            blockUI: true,
            blockUI: true,
            disableButton: true,
            buttonSelector: "#save-status",
            data: $('#editStatus').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });

    $('body').on('click', '.delete-pipeline', function() {
            var id = {{ $pipeline->id }};
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.deal.deletePipeline', ['stages' => $pipeline->stages->count(), 'deals' => $pipeline->deals->count()])",
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
                    var url = "{{ route('lead-pipeline-setting.destroy', ':id') }}";
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
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });

</script>
