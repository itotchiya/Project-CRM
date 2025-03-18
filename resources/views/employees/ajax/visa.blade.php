@php
$deleteImmigrationPermission = user()->permission('delete_immigration');
$editImmigrationPermission = user()->permission('edit_immigration');
@endphp

<!-- ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-3">
        <x-cards.data :title="__('modules.employees.visaDetails')">
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                            @if ($editImmigrationPermission == 'all'
                            || ($editImmigrationPermission == 'added' && $visa->added_by == user()->id)
                            || ($editImmigrationPermission == 'owned' && ($visa->user_id == user()->id && $visa->added_by != user()->id))
                            || ($editImmigrationPermission == 'both' && ($visa->added_by == user()->id || $visa->user_id == user()->id)))
                                <a class="dropdown-item edit-visa" data-id = "{{$visa ? $visa->id : '' }}"
                                    href="javascript:;">@lang('app.edit')</a>
                            @endif
                            @if ($deleteImmigrationPermission == 'all'
                            || ($deleteImmigrationPermission == 'added' && $visa->added_by == user()->id)
                            || ($deleteImmigrationPermission == 'owned' && ($visa->user_id == user()->id && $visa->added_by != user()->id))
                            || ($deleteImmigrationPermission == 'both' && ($visa->added_by == user()->id || $visa->user_id == user()->id)))
                            <a class="dropdown-item delete-visa" data-id = "{{$visa ? $visa->id : '' }}"
                                href="javascript:;">@lang('app.delete')</a>
                            @endif
                        </div>
                    </div>
                </x-slot>

                <x-cards.data-row :label="__('modules.employees.visaNumber')" :value="$visa->visa_number ?? '--'" />
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                        @lang('app.country')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        <span class='flag-icon flag-icon-{{ strtolower($visa->country->iso) }} flag-icon-squared'></span>
                        {{ $visa->country->name}}

                    </p>
                </div>
                <x-cards.data-row :label="__('modules.employees.issueDate')" :value=" $visa ? $visa->issue_date->format(company()->date_format) : '--'" />
                <x-cards.data-row :label="__('modules.employees.expiryDate')" :value=" $visa  ? $visa->expiry_date->format(company()->date_format) : '--'" />
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($visa->file)
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $visa->image_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
                        @else
                        --
                        @endif
                    </p>
                </div>
        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!--  ROW END -->

<script>

    $('.edit-visa').click(function() {
        var id = $(this).data('id');
        var url = "{{ route('employee-visa.edit', ':id') }}";
        url = url.replace(':id', id);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.delete-visa', function() {
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
                var url = "{{ route('employee-visa.destroy', ':id') }}";
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
                            var url = " {{ route('employees.show', $visa->user_id).'?tab=immigration' }}";
                            window.location.href = url;
                        }
                    }
                });
            }
        });
    });

</script>
