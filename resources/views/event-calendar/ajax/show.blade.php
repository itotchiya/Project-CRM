@php
$editPermission = user()->permission('edit_events');
$deletePermission = user()->permission('delete_events');
$attendeesIds = $event->attendee->pluck('user_id')->toArray();
@endphp

<style>
    #event-status2 {
        border-radius: 0 5px 5px 0;
    }

    #event-status {
        border-radius: 5px 0 0 5px;
    }
</style>

<div id="task-detail-section">
    <h3 class="heading-h1 mb-3">{{ $event->event_name }}</h3>
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey justify-content-between p-20">
                    <div class="row">
                        <div class="col-lg-8 col-10">
                            @if ($event->status == 'pending')
                                @if ($editPermission == 'all'
                                || ($editPermission == 'added' && $event->added_by == user()->id)
                                || ($editPermission == 'owned' && in_array(user()->id, $attendeesIds))
                                || ($editPermission == 'both' && (in_array(user()->id, $attendeesIds) || $event->added_by == user()->id))
                                || $event->host == user()->id
                                )
                                    <x-forms.button-primary icon="check" data-event-id="{{$event->id}}" id="event-status" value="completed"
                                                class="mr-2 mb-2 mb-lg-0 mb-md-0">
                                                @lang('app.markComplete')
                                    </x-forms.button-primary>

                                    <x-forms.button-secondary icon="times" data-event-id="{{$event->id}}" id="event-status2" value="cancelled"
                                        class="mr-3">
                                        @lang('app.markCancel')
                                    </x-forms.button-secondary>
                                @endif
                            @endif
                        </div>
                        <div class="col-lg-4 col-2 text-right">
                            <div class="dropdown">
                                <button
                                    class="btn btn-lg f-14 px-2 py-1 text-dark-grey  rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                    aria-labelledby="dropdownMenuLink" tabindex="0">
                                    @if ($editPermission == 'all'
                                        || ($editPermission == 'added' && $event->added_by == user()->id)
                                        || ($editPermission == 'owned' && (in_array(user()->id, $attendeesIds) || $event->host == user()->id))
                                        || ($editPermission == 'both' && (in_array(user()->id, $attendeesIds) || $event->added_by == user()->id || $event->host == user()->id))
                                    )
                                        <a class="dropdown-item openRightModal"
                                            href="{{ route('events.edit', $event->id) }}">@lang('app.edit')
                                        </a>
                                    @endif

                                    @if ($deletePermission == 'all'
                                        || ($deletePermission == 'added' && $event->added_by == user()->id)
                                        || ($deletePermission == 'owned' && (in_array(user()->id, $attendeesIds) || $event->added_by == user()->id))
                                        || ($deletePermission == 'both' && (in_array(user()->id, $attendeesIds) || $event->added_by == user()->id))
                                    )
                                        <a class="dropdown-item delete-event">@lang('app.delete')</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <x-cards.data-row :label="__('modules.events.eventName')" :value="$event->event_name"
                        html="true" />

                    @if (!in_array('client', user_roles()))
                        <div class="col-12 px-0 pb-3 d-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                            @lang('app.attendeesEmployee')</p>
                            <p class="mb-0 text-dark-grey f-14">
                                @foreach ($event->attendee as $item)
                                @if(in_array('employee', $item->user->roles->pluck('name')->toArray()))
                                    <div class="taskEmployeeImg rounded-circle mr-1">
                                        <img data-toggle="tooltip" data-original-title="{{ $item->user->name }}"
                                            src="{{ $item->user->image_url }}">
                                    </div>
                                @endif
                                @endforeach
                            </p>
                        </div>

                        @if (in_array('clients', user_modules()))
                            <div class="col-12 px-0 pb-3 d-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                    @lang('app.attendeesClients')</p>
                                <p class="mb-0 text-dark-grey f-14">
                                    @foreach ($event->attendee as $item)
                                    @if(in_array('client', $item->user->roles->pluck('name')->toArray()))
                                        <div class="taskEmployeeImg rounded-circle mr-1">
                                            <img data-toggle="tooltip" data-original-title="{{ $item->user->name }}"
                                                src="{{ $item->user->image_url }}">
                                        </div>
                                    @endif
                                    @endforeach
                                </p>
                            </div>
                        @endif
                    @endif

                    <div class="col-12 px-0 pb-3 d-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                            @lang('app.host')</p>
                        <p class="mb-0 text-dark-grey f-14">
                            @if ($event->user)
                            <div class="taskEmployeeImg rounded-circle mr-1">
                                <img data-toggle="tooltip"
                                data-original-title="{{ $event->user->name }}"
                                src="{{ $event->user->image_url }}">
                            </div>
                            @else
                            --
                            @endif
                        </p>
                    </div>

                    <x-cards.data-row :label="__('app.description')" :value="$event->description"
                        html="true" />
                    <x-cards.data-row :label="__('app.where')" :value="$event->where"
                        html="true" />
                    <x-cards.data-row :label="__('modules.events.startOn')"
                        :value="$event->start_date_time->translatedFormat(company()->date_format. ' - '.company()->time_format)"
                        html="true" />
                    <x-cards.data-row :label="__('modules.events.endOn')"
                        :value="$event->end_date_time->translatedFormat(company()->date_format. ' - '.company()->time_format)"
                        html="true" />
                        @php
                        $url = str_starts_with($event->event_link, 'http') ? $event->event_link : 'http://'.$event->event_link;
                            $link = "<a href=".$url." style='color:black; cursor: pointer;' target='_blank'>$event->event_link</a>";
                        @endphp

                    @if ($event->status)
                        <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                            <p class="mb-0 text-lightest f-14 w-30 ">@lang('app.status')</p>
                            @if ($event->status == 'pending')
                                <p class="mb-0 text-dark-grey f-14 w-70 text-wrap"><i class="fa fa-circle mr-1 text-yellow f-10"></i>{{ ucfirst($event->status) }}</p>
                            @elseif ($event->status == 'completed')
                                <p class="mb-0 text-dark-grey f-14 w-70 text-wrap"><i class="fa fa-circle mr-1 text-dark-green f-10"></i>{{ ucfirst($event->status) }}</p>
                            @elseif ($event->status == 'cancelled')
                                <p class="mb-0 text-dark-grey f-14 w-70 text-wrap"><i class="fa fa-circle mr-1 text-red f-10"></i>{{ ucfirst($event->status) }}</p>
                            @endif
                        </div>
                    @endif
                    @if ($event->note)
                    <x-cards.data-row :label="__('app.note')" :value="$event->note"
                        html="true" />
                    @endif
                    <x-cards.data-row :label="__('modules.events.eventLink')"
                    html="true" :value="$link"/>
                    @if ($event->files->count() > 0)
                        <x-cards.data-row :label="__('app.file')"
                        html="true" :value="''"/>
                        <div div class="d-flex flex-wrap mt-3" id="event-file-list">
                            @forelse($event->files as $file)
                                <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
                                    <x-file-view-thumbnail :file="$file"></x-file-view-thumbnail>

                                        <x-slot name="action">
                                            <div class="dropdown ml-auto file-action">
                                                <button class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-h"></i>
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                                    aria-labelledby="dropdownMenuLink" tabindex="0">
                                                        @if ($file->icon == 'images')
                                                            <a class="img-lightbox cursor-pointer d-block text-dark-grey f-13 pt-3 px-3" data-image-url="{{ $file->file_url }}" href="javascript:;">@lang('app.view')</a>
                                                        @else
                                                            <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 " target="_blank" href="{{ $file->file_url }}">@lang('app.view')</a>
                                                        @endif
                                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                                            href="{{ route('event-files.download', md5($file->id)) }}">@lang('app.download')</a>

                                                        <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-file"
                                                            data-row-id="{{ $file->id }}" href="javascript:;">@lang('app.delete')</a>
                                                </div>
                                            </div>
                                        </x-slot>

                                </x-file-card>
                            @empty
                            <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file" />
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>

$('body').on('click', '.delete-event', function() {
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            @if ($event->parent_id)
            input: 'radio',
            inputValue: 'this',
            inputOptions: {
                'this': `@lang('app.thisEvent')`,
                'all': `@lang('app.allEvent')`
            },
            @endif
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
                var url = "{{ route('events.destroy', $event->id) }}";

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE',
                        @if ($event->parent_id)
                        'delete': result.value,
                        @endif
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


    $('body').on('click', '.delete-file', function() {
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
                var url = "{{ route('event-files.destroy', ':id') }}";
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
                            $('#event-file-list').html(response.view);
                        }
                    }
                });
            }
        });
    });

    $('#event-status2').click(function () {
        var id = $(this).data('event-id');
        var url = "{{ route('events.event_status_note', ':id') }}?status=cancelled";
        url = url.replace(':id', id);
        $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_DEFAULT, url);
    });

    $('#event-status').click(function () {
        var id = $(this).data('event-id');
        var url = "{{ route('events.event_status_note', ':id') }}?status=completed";
        url = url.replace(':id', id);
        $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_DEFAULT, url);
    });

</script>
