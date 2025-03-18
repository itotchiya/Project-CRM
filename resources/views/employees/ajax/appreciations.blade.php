@php
    $managePermission = user()->permission('view_appreciation');
    $addAppreciationPermission = user()->permission('add_appreciation');
    $editAppreciationPermission = user()->permission('edit_appreciation');
    $deleteAppreciationPermission = user()->permission('delete_appreciation');
    $showAppreciationPermission = user()->permission('view_appreciation');
@endphp
    <!-- TAB CONTENT START -->
<div class="row py-0 py-md-0 py-lg-3">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">

        @if ($addAppreciationPermission == 'all')
            <div class="d-flex justify-content-between action-bar mb-3">
                <x-forms.link-primary :link="route('appreciations.create').'?empid='.$employee->id"
                                      data-redirect-url="{{ url()->full() }}" class="mr-3 openRightModal float-left"
                                      icon="plus">
                    @lang('modules.appreciations.addAppreciation')
                </x-forms.link-primary>
            </div>
        @endif

        <x-cards.data :title="__('modules.appreciations.appreciation')">

            <div class="table-responsive">
                <x-table class="table-bordered">
                    <x-slot name="thead">
                        <th>@lang('modules.appreciations.appreciationType')</th>
                        <th>@lang('modules.appreciations.awardDate')</th>
                        <th class="text-right">@lang('app.action')</th>
                    </x-slot>

                    @forelse ($appreciations as $count => $appreciation)
                        <tr class="tableRow{{$appreciation->id}}">
                            <td>
                                <x-award-icon :award="$appreciation->award"/>
                                <a class="openRightModal text-dark-grey"
                                   href="{{ route('appreciations.show', $appreciation->id) }}">
                                    <span class="align-self-center ml-2">{{ $appreciation->award->title }}</span>
                                </a>
                            </td>
                            <td>{{ $appreciation->award_date->translatedFormat($company->date_format) }}</td>
                            <td class="text-right">
                                @if(($showAppreciationPermission == 'all' || ($showAppreciationPermission == 'added' && user()->id == $appreciation->added_by) || ($showAppreciationPermission == 'owned' && user()->id == $appreciation->award_to) || ($showAppreciationPermission == 'both' && ($appreciation->added_by == user()->id || user()->id == $appreciation->award_to)))
                                    || ($editAppreciationPermission == 'all' || ($editAppreciationPermission == 'added' && user()->id == $appreciation->added_by) || ($editAppreciationPermission == 'owned' && user()->id == $appreciation->award_to) || ($editAppreciationPermission == 'both' && ($appreciation->added_by == user()->id || user()->id == $appreciation->award_to)))
                                     || ($deleteAppreciationPermission == 'all' || ($deleteAppreciationPermission == 'added' && user()->id == $appreciation->added_by) || ($deleteAppreciationPermission == 'owned' && user()->id == $appreciation->award_to) || ($deleteAppreciationPermission == 'both' && ($appreciation->added_by == user()->id || user()->id == $appreciation->award_to))))
                                    <div class="task_view">
                                        <div class="dropdown">
                                            <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle"
                                               type="link"
                                               id="dropdownMenuLink-{{$count}}" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false" data-boundary="viewport">
                                                <i class="icon-options-vertical icons"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right"
                                                 aria-labelledby="dropdownMenuLink-{{$count}}" tabindex="0">

                                                @if($showAppreciationPermission == 'all' || ($showAppreciationPermission == 'added' && user()->id == $appreciation->added_by) || ($showAppreciationPermission == 'owned' && user()->id == $appreciation->award_to) || ($showAppreciationPermission == 'both' && ($appreciation->added_by == user()->id || user()->id == $appreciation->award_to)))
                                                    <a class="dropdown-item openRightModal"
                                                       href="{{ route('appreciations.show', $appreciation->id) }}">
                                                        <i class="fa fa-eye mr-2"></i>
                                                        @lang('app.view')
                                                    </a>
                                                @endif

                                                @if($editAppreciationPermission == 'all' || ($editAppreciationPermission == 'added' && user()->id == $appreciation->added_by) || ($editAppreciationPermission == 'owned' && user()->id == $appreciation->award_to) || ($editAppreciationPermission == 'both' && ($appreciation->added_by == user()->id || user()->id == $appreciation->award_to)))
                                                    <a class="dropdown-item openRightModal"
                                                       data-redirect-url="{{ url()->full() }}"
                                                       href="{{ route('appreciations.edit', $appreciation->id) }}">
                                                        <i class="fa fa-edit mr-2"></i>
                                                        @lang('app.edit')
                                                    </a>
                                                @endif
                                                @if($deleteAppreciationPermission == 'all' || ($deleteAppreciationPermission == 'added' && user()->id == $appreciation->added_by) || ($deleteAppreciationPermission == 'owned' && user()->id == $appreciation->award_to) || ($deleteAppreciationPermission == 'both' && ($appreciation->added_by == user()->id || user()->id == $appreciation->award_to)))
                                                    <a class="dropdown-item delete-table-row"
                                                       data-redirect-url="{{ url()->full() }}" href="javascript:;"
                                                       data-user-id="{{ $appreciation->id }}">
                                                        <i class="fa fa-trash mr-2"></i>
                                                        @lang('app.delete')
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-cards.no-record-found-list colspan="5"/>

                    @endforelse
                </x-table>
            </div>

        </x-cards.data>
    </div>
</div>
<!-- TAB CONTENT END -->

<script>
    $('body').on('click', '.delete-table-row', function () {
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
                var url = "{{ route('appreciations.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
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

</script>
