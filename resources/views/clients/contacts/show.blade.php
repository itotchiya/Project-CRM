@php
$editDepartmentPermission = user()->permission('edit_department');
$deleteDepartmentPermission = user()->permission('delete_department');
@endphp

<div id="department-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('app.showContact')</h3>
                        </div>
                        <div class="col-md-2 col-2 text-right">
                            <div class="dropdown">
                                    <button
                                        class="btn btn-lg f-14 px-2 py-1 text-dark-grey  rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">
                                        @if($editClientPermission == 'all' || ($editClientPermission == 'added' && user()->id == $row->added_by) || ($editClientPermission == 'both' && user()->id == $contact->added_by))
                                            <a class="dropdown-item openRightModal"
                                            data-redirect-url="{{ url()->previous() }}"
                                            href="{{ route('client-contacts.edit', $contact->id) }}">@lang('app.edit')</a>
                                        @endif
                                        @if($deleteClientPermission == 'all' || ($deleteClientPermission == 'added' && user()->id == $contact->added_by) || ($deleteClientPermission == 'both' && user()->id == $contact->added_by))
                                        <a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="{{$contact->id }}">@lang('app.delete')</a>
                                        @endif
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <x-cards.data-row :label="__('app.name')" :value="$contact->contact_name"
                        html="true" />
                        <x-cards.data-row :label="__('app.email')" :value="$contact->email"
                        html="true" />
                        <x-cards.data-row :label="__('app.phone')" :value="$contact->phone ?? '--'"
                        html="true" />
                        <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('modules.employees.gender')</p>
                            <p class="mb-0 text-dark-grey f-14 w-70">
                                <x-gender :gender='$client->gender' />
                            </p>
                        </div>
                        <x-cards.data-row :label="__('modules.accountSettings.companyAddress')" :value="$contact->address"
                            html="true" />

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('user-id');
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
                var url = "{{ route('client-contacts.destroy', ':id') }}";
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
