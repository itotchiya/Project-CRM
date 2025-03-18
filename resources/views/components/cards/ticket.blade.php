<div class="card ticket-message rounded-0 border-0  @if (user()->id == $user->id) bg-white-shade @endif" id="message-{{ $message->id }}" @if ($message->type == 'note') style="background-image: linear-gradient(#FFB4B452, #FFB4B452);" @endif>
    <div class="card-horizontal">
        <div class="card-img">
            <a
                href="{{ !is_null($user->employeeDetail) ? route('employees.show', $user->id) : route('clients.show', $user->id) }}"><img
                    class="" src="{{ $user->image_url }}" alt="{{ $user->name }}"></a>
        </div>
        <div class="card-body border-0 pl-0">
            <div class="d-flex">
                <a href="{{ !is_null($user->employeeDetail) ? route('employees.show', $user->id) : route('clients.show', $user->id) }}">
                    <h4 class="card-title f-13 f-w-500 text-dark mr-3">{{ $user->name }}</h4>
                </a>
                <p class="card-date f-11 text-lightest mb-0">
                    {{ $message->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                </p>

                @if ($user->id == user()->id || in_array('admin', user_roles()))
                    <div class="dropdown ml-auto message-action">
                        <button class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        @if ($message->type == 'note')
                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item edit-message bi bi-pencil"
                                    data-row-id="{{ $message->id }}" data-user-id="{{ $user->id }}" href="javascript:;">&nbsp;&nbsp; @lang('app.edit')</a>
                                <a class="dropdown-item delete-message bi bi-trash"
                                    data-row-id="{{ $message->id }}" data-user-id="{{ $user->id }}" href="javascript:;">&nbsp;&nbsp; @lang('app.delete')</a>
                            </div>
                        @else
                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item delete-message"
                                data-row-id="{{ $message->id }}" data-user-id="{{ $user->id }}" href="javascript:;">@lang('app.delete')</a>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
            @if ($message->message != '')
                <div class="card-text text-dark-grey text-justify mb-2 note-message" id="{{ $message->id }}">
                    <div class="notified-message">
                    @if ($message->type == 'note')
                        <strong class="f-12">@lang('app.notifiedTo')</strong>
                        @foreach ($message->ticketReplyUsers as $item)
                            <span class="f-12">{{ $item->user->email }}</span>@if (!$loop->last),@endif
                        @endforeach
                        <br>
                    @endif
                    </div>
                    <span class="ql-editor f-13 px-0">{!! nl2br($message->message) !!}</span>
                </div>
            @endif

            <div class="form-control edit-note-message d-none" id="text-{{$message->id}}">
                <div class="card-text text-dark-grey text-justify mb-2" id="message-{{ $message->id }}">
                    <div id="description-note{{$message->id}}">{!! nl2br($message->message) !!}</div>
                </div>
                <div class="form-group">
                    <div class="my-1 form-group">
                        <a class="f-15 f-w-500 edit-ticket-file" href="javascript:;" id="edit-note-file"><i
                                class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')</a>
                    </div>

                    <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2 upload-edit-note-section d-none"
                        fieldLabel=""
                        fieldName="file[]" fieldId="edit-note-file-upload-dropzone-{{$message->id}}"/>
                </div>

                <div class="footer text-right">
                    <button class="btn btn-secondary btn-sm rounded f-14 p-2 update-buttons" type="button" id="cancel-notes">@lang('app.cancel')</button>
                    <button class="btn btn-primary btn-sm rounded f-14 p-2 update-buttons" type="button" id="save-notes">@lang('app.update')</button>
                </div>
            </div>

            {{ $slot }}

            <div class="d-flex flex-wrap">
                @foreach ($message->files as $file)
                    <x-file-card :fileName="$file->filename"
                        :dateAdded="$file->created_at->diffForHumans()">
                        <x-file-view-thumbnail :file="$file"></x-file-view-thumbnail>

                        <x-slot name="action">
                            <div class="dropdown ml-auto file-action">
                                <button
                                    class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                    aria-labelledby="dropdownMenuLink" tabindex="0">

                                    <a class="dropdown-item"
                                        target="_blank"
                                        href="{{ $file->file_url }}">@lang('app.view')</a>

                                    <a class="dropdown-item"
                                        href="{{ route('ticket-files.download', md5($file->id)) }}">@lang('app.download')</a>

                                    @if (user()->id == $user->id || in_array('admin', user_roles()))
                                        <a class="dropdown-item delete-file"
                                            data-row-id="{{ $file->id }}"
                                            href="javascript:;">@lang('app.delete')</a>
                                    @endif
                                </div>
                            </div>
                        </x-slot>
                    </x-file-card>
                @endforeach
            </div>

        </div>

    </div>
</div>
<!-- card end -->
