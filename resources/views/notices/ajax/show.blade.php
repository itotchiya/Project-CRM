@php
$editPermission = user()->permission('edit_notice');
$deletePermission = user()->permission('delete_notice');
@endphp
<div id="notice-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
                    <div class="row">
                        <div class="col-lg-10 col-10">
                            <h3 class="heading-h1 mb-3">@lang('app.noticeDetails')</h3>
                        </div>
                        <div class="col-lg-2 col-2 text-right">

                            @if (!in_array('client', user_roles()) && (($editPermission == 'all' || ($editPermission == 'added' && $notice->added_by == user()->id) || ($editPermission == 'owned' && in_array($notice->to, user_roles())) || ($editPermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id))) || ($deletePermission == 'all' || ($deletePermission == 'added' && $notice->added_by == user()->id) || ($deletePermission == 'owned' && in_array($notice->to, user_roles())) || ($deletePermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id)))))
                                <div class="dropdown">
                                    <button
                                        class="btn btn-lg f-14 px-2 py-1 text-dark-grey  rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">

                                        @if ($editPermission == 'all' || ($editPermission == 'added' && $notice->added_by == user()->id) || ($editPermission == 'owned' && in_array($notice->to, user_roles())) || ($editPermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id)))
                                            <a class="dropdown-item openRightModal"
                                                href="{{ route('notices.edit', $notice->id) }}">@lang('app.edit')</a>
                                        @endif
                                        @if ($deletePermission == 'all' || ($deletePermission == 'added' && $notice->added_by == user()->id) || ($deletePermission == 'owned' && in_array($notice->to, user_roles())) || ($deletePermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id)))
                                            <a class="dropdown-item delete-notice">@lang('app.delete')</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <x-cards.data-row :label="__('modules.notices.noticeHeading')" :value="$notice->heading" />
                    <x-cards.data-row :label="__('app.date')"
                        :value="$notice->created_at->translatedFormat(company()->date_format)" />

                    <x-cards.data-row :label="__('app.to')" :value="__('app.'.$notice->to)" />

                    <div class="col-12 px-0 pb-3 d-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        {{$notice->to == 'employee' ? __('app.employee') : __('app.client')}}</p>
                        <p class="mb-0 text-dark-grey f-14">
                            @if ($notice->to == 'employee' && count($noticeEmployees) > 0)
                                @foreach ($noticeEmployees as $emp)
                                    <x-employee-image :user="$emp" />
                                @endforeach
                            @elseif($notice->to == 'client' && count($noticeClients) > 0)
                                @foreach ($noticeClients as $client)
                                    <x-client-image :user="$client" />
                                @endforeach
                            @else
                                --
                            @endif
                        </p>
                    </div>

                    <x-cards.data-row :label="__('app.description')" :value="!empty($notice->description) ? $notice->description : '--'" html="true" />

                    @if (!is_null($notice->attachment))
                        <x-cards.data-row :label="__('app.viewAttachment')"
                            value='<a target="_blank" href="{{ $notice->file_url }}" title="@lang('app.viewAttachment')">
                            <span class="btn btn-sm btn-info"> @lang('app.viewAttachment') </span>
                            </a>'/>
                    @endif

                    @if (in_array('admin', user_roles()))
                        <div class="col-12 px-0 pb-3 d-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('app.readBy')</p>
                            <p class="mb-0 text-dark-grey f-14">
                                @if (count($readMembers) > 0)
                                    @foreach ($readMembers as $item)
                                        @if($notice->to == 'employee')
                                            <x-employee-image :user="$item->user" />
                                        @else
                                            <x-client-image :user="$item->user" />
                                        @endif
                                    @endforeach
                                @else
                                    --
                                @endif
                            </p>
                        </div>

                    @endif

                    <div class="col-12 px-0 mt-3 pb-3 d-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                            @lang('app.file')</p>
                        <div class="d-flex flex-wrap" id="notice-file-list">
                            @php
                                $filesShowCount = 0;
                            @endphp
                            @forelse($notice->files as $file)
                                @php
                                    $filesShowCount++;
                                @endphp
                                <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
                                    <x-file-view-thumbnail :file="$file"></x-file-view-thumbnail>
                                    <x-slot name="action">
                                        <div class="dropdown ml-auto file-action">
                                            <button class="btn btn-lg f-14 p-0 text-lightest text-capitalize rounded  dropdown-toggle"
                                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-ellipsis-h"></i>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                                @if ($file->icon = 'images')
                                                    @if ($file->icon == 'images')
                                                        <a class="img-lightbox cursor-pointer d-block text-dark-grey f-13 pt-3 px-3" data-image-url="{{ $file->file_url }}" href="javascript:;">@lang('app.view')</a>
                                                    @else
                                                        <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 " target="_blank" href="{{ $file->file_url }}">@lang('app.view')</a>
                                                    @endif
                                                @endif
                                                <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                                href="{{ route('notice_files.download', md5($file->id)) }}">@lang('app.download')</a>

                                                @if ($deletePermission == 'all'
                                                        || ($deletePermission == 'added' && $notice->added_by == user()->id)
                                                        || ($deletePermission == 'owned' && in_array($notice->to, user_roles()))
                                                        || ($deletePermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id)))
                                                    <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-file"
                                                    data-row-id="{{ $file->id }}" href="javascript:;">@lang('app.delete')</a>
                                                @endif
                                            </div>
                                        </div>
                                    </x-slot>
                                </x-file-card>
                            @empty
                                <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file"/>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.delete-notice', function() {
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
                var url = "{{ route('notices.destroy', $notice->id) }}";

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

    $('body').on('click', '.delete-file', function () {
        var id = $(this).data('row-id');
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
                var url = "{{ route('notice-files.destroy', ':id') }}";
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
                            $('#notice-file-list').html(response.view);
                        }
                    }
                });
            }
        });
    });
</script>
