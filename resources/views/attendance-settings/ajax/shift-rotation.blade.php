@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

<div class="d-flex flex-column w-tables rounded bg-white table-responsive">
    {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
</div>

@push('scripts')
    @include('sections.datatable_js')
    <script>
        const showTable = () => {
            window.LaravelDataTables["shift-rotation-table"].draw(true);
        }

        $('body').on('click', '#manageEmployees', function() {
            var rotationId = $(this).data('rotation-id');
            var url = "{{ route('shift-rotations.manage_rotation_employee', ':id') }}";
            url = url.replace(':id', rotationId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.delete-shift-rotation', function() {
            let id = $(this).data('rotation-id');

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
                    var url = "{{ route('shift-rotations.destroy', ':id') }}";
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
                                showTable();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('change', '.change-rotation-status', function() {
            let status = $(this).val();
            let rotationId = $(this).data('rotation-id');

            var url = "{{ route('shift-rotations.change_status') }}";
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                type: 'POST',
                blockUI: true,
                data: {
                    '_token': token,
                    id: rotationId,
                    status: status,
                    sortBy: 'id'
                },
                success: function(response) {
                    if (response.status == "success") {
                        showTable();
                    }
                }
            });
        });
    </script>
@endpush
