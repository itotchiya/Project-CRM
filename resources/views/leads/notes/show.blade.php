@php
    $editLeadNotePermission = user()->permission('edit_deal_note');
    $deleteLeadNotePermission = user()->permission('delete_deal_note');
@endphp

<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="__('app.note').' '.__('app.details')" class=" mt-4">
            <x-slot name="action">
                <div class="dropdown">
                    <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                        @if ($editLeadNotePermission == 'all' || ($editLeadNotePermission == 'added' && $note->added_by == user()->id))
                            <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 edit-note openRightModal"
                                href="{{ route('deal-notes.edit', $note->id) }}" data-row-id="{{ $note->id }}">@lang('app.edit')</a>
                        @endif

                        @if ($deleteLeadNotePermission == 'all' || ($deleteLeadNotePermission == 'added' && $note->added_by == user()->id))
                            <a class="cursor-pointer d-block text-dark-grey f-13 py-1 px-3 delete-note-lead"
                                data-id="{{ $note->id }}"
                                href="javascript:;">@lang('app.delete')</a>
                        @endif
                    </div>
                </div>
            </x-slot>

            <x-cards.data-row :label="__('modules.client.noteTitle')"
                :value="$note->title" />

            <x-cards.data-row :label="__('modules.client.noteDetail')" :value="$note->details" html="true" />

        </x-cards.data>
    </div>
</div>

<script>
    $('body').on('click', '.delete-note-lead', function() {
        var id = $(this).data('id');
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
                var url = "{{ route('deal-notes.destroy', ':id') }}";
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
                            window.location.href = response.redirectUrl;
                        }
                    }
                });
            }
        });
    });
</script>
