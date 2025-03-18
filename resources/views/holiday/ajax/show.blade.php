@php
    $editPermission = user()->permission('edit_holiday');
    $deletePermission = user()->permission('delete_holiday');

@endphp
<div id="holiday-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="bg-white border-0 card b-shadow-4">
                <div class="p-20 bg-white card-header border-bottom-grey  justify-content-between">
                    <div class="row">
                        <div class="col-lg-10 col-10">
                            <h3 class="mb-3 heading-h1">@lang('app.holidayDetails')</h3>
                        </div>
                        <div class="text-right col-lg-2 col-2">
                            @if (
                                $editPermission == 'all' ||
                                    ($editPermission == 'added' && $holiday->added_by == user()->id) ||
                                    ($deletePermission == 'all' || ($deletePermission == 'added' && $holiday->added_by == user()->id)))
                                <div class="dropdown">
                                    <button
                                        class="px-2 py-1 rounded btn btn-lg f-14 text-dark-grey  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>

                                    <div class="p-0 rounded dropdown-menu dropdown-menu-right border-grey b-shadow-4"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">
                                        @if ($editPermission == 'all' || ($editPermission == 'added' && $holiday->added_by == user()->id))
                                            <a class="dropdown-item openRightModal"
                                                href="{{ route('holidays.edit', $holiday->id) }}">@lang('app.edit')</a>
                                        @endif
                                        @if ($deletePermission == 'all' || ($deletePermission == 'added' && $holiday->added_by == user()->id))
                                            <a class="dropdown-item delete-holiday">@lang('app.delete')</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <x-cards.data-row :label="__('app.date')" :value="$holiday->date->translatedFormat(company()->date_format)" html="true" />
                    <x-cards.data-row :label="__('modules.holiday.occasion')" :value="$holiday->occassion" html="true" />
                        {{-- @dd($department) --}}
                    <x-cards.data-row :label="__('app.department')" :value="$department ? $department : '--'" html="true" />
                    <x-cards.data-row :label="__('app.designation')" :value="$designation ? $designation : '--'" html="true" />

                    <x-cards.data-row :label="__('modules.employees.employmentType')" :value="$employment_type ? $employment_type : '--'" />





                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.delete-holiday', function() {
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
                var url = "{{ route('holidays.destroy', $holiday->id) }}";

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
