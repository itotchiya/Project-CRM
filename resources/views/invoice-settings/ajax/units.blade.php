<div class="col-xl-12 col-lg-12 col-md-12 w-100 p-20">
    <div class="table-responsive">
        <x-table class="table-bordered">
            <x-slot name="thead">
                <th>@lang('app.units')</th>
                <th>&nbsp;</th>
                <th class="text-right pr-20">@lang('app.action')</th>
            </x-slot>
            @forelse($unitTypes as $unit)
                <tr class="row{{ $unit->id }}">
                    <td>
                        {{ $unit->unit_type }}
                    </td>
                    <td>
                        <x-forms.radio fieldId="{{ $unit->id }}" class="set_default_unit"
                            data-unit-id="{{ $unit->id }}" :fieldLabel="__('app.default')" fieldName="unit_type"
                            fieldValue="{{ $unit->id }}" :checked="$unit->default == 1">
                        </x-forms.radio>
                    </td>
                    <td class="text-right pr-20">
                        <div class="task_view mr-1">
                            <a href="javascript:;" data-unit-id="{{ $unit->id }}"
                                class="edit-unit task_view_more d-flex align-items-center"> <i class="fa fa-edit mr-1"></i> @lang('app.edit')
                            </a>
                        </div>
                        @if (!($unit->default == 1))
                            <div class="task_view">
                                <a href="javascript:;" data-unit-id="{{ $unit->id }}"
                                    class="delete-unit task_view_more d-flex align-items-center justify-content-center dropdown-toggle">
                                    <i class="fa fa-trash mr-1"></i> @lang('app.delete')
                                </a>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <x-cards.no-record icon="user" :message="__('messages.noAgentAdded')" />
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>
</div>

<script>

    $('.edit-unit').click(function() {
        var unitID = $(this).data('unit-id');
        var url = "{{ route('unit-type.edit', ':id') }}";
        url = url.replace(':id', unitID);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    /* delete shift */
    $('.delete-unit').click(function() {
        var id = $(this).data('unit-id');
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
                var url = "{{ route('unit-type.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('.row' + id).fadeOut(100);
                        }
                    }
                });
            }
        });
    });

    $('.set_default_unit').click(function() {
        var unitID = $(this).data('unit-id');
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: "{{ route('unit-type.set_default') }}",
            type: "POST",
            data: {
                unitID: unitID,
                _token: token
            },
            blockUI: true,
            container: '#editSettings',
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        });
    });
</script>
