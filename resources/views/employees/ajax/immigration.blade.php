@php
$addImmigrationPermission = user()->permission('add_immigration');
$viewImmigrationPermission = user()->permission('view_immigration');
$deleteImmigrationPermission = user()->permission('delete_immigration');
$editImmigrationPermission = user()->permission('edit_immigration');
@endphp

<!-- PASSPORT ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-5">
        @if(is_null($passport))
            @if ($addImmigrationPermission == 'all'
                || ($addImmigrationPermission == 'owned' && ($employee->id == user()->id))
                )
                <x-forms.button-primary class="mr-3 add-passport mb-3   " icon="plus">
                    @lang('app.addPassport')
                </x-forms.button-primary>
            @endif
        @endif
        <x-cards.data :title="__('modules.employees.passportDetails')">

            @if($passport)
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                            @if ($editImmigrationPermission == 'all'
                            || ($editImmigrationPermission == 'added' && $passport->added_by == user()->id)
                            || ($editImmigrationPermission == 'owned' && ($passport->user_id == user()->id && $passport->added_by != user()->id))
                            || ($editImmigrationPermission == 'both' && ($passport->added_by == user()->id || $passport->user_id == user()->id)))
                                <a class="dropdown-item edit-passport" data-id = "{{$passport->id ? $passport->id : '' }}"
                                    href="javascript:;">@lang('app.edit')</a>
                            @endif

                            @if ($deleteImmigrationPermission == 'all'
                            || ($deleteImmigrationPermission == 'added' && $passport->added_by == user()->id)
                            || ($deleteImmigrationPermission == 'owned' && ($passport->user_id == user()->id && $passport->added_by != user()->id))
                            || ($deleteImmigrationPermission == 'both' && ($passport->added_by == user()->id || $passport->user_id == user()->id)))
                                <a class="dropdown-item delete-passport" data-id = "{{$passport->id ? $passport->id : '' }}"
                                    href="javascript:;">@lang('app.delete')</a>
                            @endif
                        </div>

                    </div>
                </x-slot>

                <x-cards.data-row :label="__('modules.employees.passportNumber')" :value="$passport->passport_number ?? '--'" />
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                        @lang('app.nationality')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        <span class='flag-icon flag-icon-{{ strtolower($passport->country->iso) }} flag-icon-squared'></span>
                        {{ $passport->country->nationality }}   {{ '('. $passport->country->name . ')' }}

                    </p>
                </div>
                <x-cards.data-row :label="__('modules.employees.issueDate')" :value=" $passport->issue_date ? $passport->issue_date->format(company()->date_format) : '--'" />
                <x-cards.data-row :label="__('modules.employees.expiryDate')" :value=" $passport->expiry_date  ? $passport->expiry_date->format(company()->date_format) : '--'" />
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($passport->file)
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $passport->image_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
                        @else
                        --
                        @endif

                    </p>
                </div>

            @else
                <x-cards.no-record-found-list colspan="5"/>
            @endif
        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- PASSPORT ROW END -->

<!-- VISA ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-5">

        @if ($addImmigrationPermission == 'all'
            || ($addImmigrationPermission == 'owned' && ($employee->id == user()->id))
            )
            <x-forms.button-primary icon="plus" id="add-visa" class="mb-3">
                @lang('app.addVisa')
            </x-forms.button-primary>
        @endif

            <x-cards.data :title="__('modules.employees.visaDetails')"
                otherClasses="border-0 p-0 d-flex justify-content-between align-items-center table-responsive-sm">
                <x-table class="border-0 pb-3 admin-dash-table table-hover">

                    <x-slot name="thead">
                        <th class="pl-20">#</th>
                        <th>@lang('modules.employees.visaNumber')</th>
                        <th>@lang('app.country')</th>
                        <th>@lang('modules.employees.issueDate')</th>
                        <th>@lang('modules.employees.expiryDate')</th>
                        <th class="text-right pr-20">@lang('app.action')</th>
                    </x-slot>

                    @forelse($visa as $key => $visaValue)
                        <tr id="row-{{ $visaValue->id }}">
                            <td class="pl-20">{{ $key + 1 }}</td>
                            <td>
                                {{ $visaValue->visa_number }}
                            </td>
                            <td>
                                {{$visaValue->country->name}}
                            </td>
                            <td>
                                {{ $visaValue->issue_date->format(company()->date_format)}}
                            </td>
                            <td>
                                {{ $visaValue->expiry_date->format(company()->date_format)}}
                            </td>
                            <td class="text-right pr-20">
                                <div class="task_view">
                                    <div class="dropdown">
                                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle"
                                            type="link" id="dropdownMenuLink-3" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-options-vertical icons"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if ($viewImmigrationPermission == 'all'
                                            || ($viewImmigrationPermission == 'added' && $visaValue->added_by == user()->id)
                                            || ($viewImmigrationPermission == 'owned' && ($visaValue->user_id == user()->id && $visaValue->added_by != user()->id))
                                            || ($viewImmigrationPermission == 'both' && ($visaValue->added_by == user()->id || $visaValue->user_id == user()->id)))
                                                <a class="dropdown-item openRightModal" href="{{ route('employee-visa.show', $visaValue->id ) }}"
                                                    data-id="{{ $visaValue->id }}">
                                                    <i class="fa fa-eye mr-2"></i>
                                                    @lang('app.view')
                                                </a>
                                            @endif
                                            @if ($editImmigrationPermission == 'all'
                                            || ($editImmigrationPermission == 'added' && $visaValue->added_by == user()->id)
                                            || ($editImmigrationPermission == 'owned' && ($visaValue->user_id == user()->id && $visaValue->added_by != user()->id))
                                            || ($editImmigrationPermission == 'both' && ($visaValue->added_by == user()->id || $visaValue->user_id == user()->id)))
                                                <a class="dropdown-item edit-visa"
                                                    data-id="{{ $visaValue->id }}" href="javascript:;">
                                                    <i class="fa fa-edit mr-2"></i>
                                                    @lang('app.edit')
                                                </a>
                                            @endif
                                            @if ($deleteImmigrationPermission == 'all'
                                            || ($deleteImmigrationPermission == 'added' && $visaValue->added_by == user()->id)
                                            || ($deleteImmigrationPermission == 'owned' && ($visaValue->user_id == user()->id && $visaValue->added_by != user()->id))
                                            || ($deleteImmigrationPermission == 'both' && ($visaValue->added_by == user()->id || $visaValue->user_id == user()->id)))
                                                <a class="dropdown-item delete-visa" href="javascript:;"
                                                    data-id="{{ $visaValue->id }}">
                                                    <i class="fa fa-trash mr-2"></i>
                                                    @lang('app.delete')
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-cards.no-record-found-list colspan="6"/>
                    @endforelse
                </x-table>
            </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- VISA ROW END -->

<script>

    // Visa Start
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
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });

    $('#add-visa').click(function() {
        var url = "{{ route('employee-visa.create').'?empid='.$employee->id }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })

    $('.edit-visa').click(function() {
        var id = $(this).data('id');
        var url = "{{ route('employee-visa.edit', ':id') }}";
        url = url.replace(':id', id);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    // Visa End

    // Passport Start
    $('.add-passport').click(function(){
        var url = "{{ route('passport.create').'?empid='.$employee->id }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

    $('.edit-passport').click(function(){
        var id = $(this).data('id');
        var url = "{{ route('passport.edit', ':id').'?empid='.$employee->id }}";
        url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.delete-passport', function () {

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

                var url = "{{ route('passport.destroy', ':id') }}";
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
                    success: function (response) {
                        if (response.status == "success") {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });
    // Passport End

</script>
