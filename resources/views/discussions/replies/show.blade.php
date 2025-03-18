<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<!-- START -->
<div class="d-flex justify-content-between align-items-center p-3 border-bottom-grey rounded-top bg-white">
    <span>
        <p class="f-15 f-w-500 mb-0">{{ $discussion->title }}</p>
        <p class="f-11 text-lightest mb-0">@lang('modules.tickets.requestedOn')
            {{ $discussion->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
        </p>
    </span>
    <span>
        <p class="mb-0 ">
            @if ($discussion->category)
                <x-status :style="'color:'.$discussion->category->color" :value="$discussion->category->name" />
            @endif
        </p>
    </span>
</div>
<!-- END -->
@foreach ($discussion->replies as $key => $message)
    @php
        $replyUser = $message->user;
    @endphp
    {{-- clients.show --}}
    <div class="card ticket-message border-0 rounded-bottom
        @if (user()->id == $replyUser->id) bg-white-shade @endif
        " id="message-{{ $message->id }}">
        <div class="card-horizontal">
            <div class="card-img">
                <a href="{{ route(($replyUser->hasRole('client') ? 'clients.show' : 'employees.show'), $replyUser->id) }}"><img class=""
                        src="{{ $replyUser->image_url }}" alt="{{ $replyUser->name }}"></a>
            </div>
            <div class="card-body border-0 pl-0">
                <div class="d-flex">
                    <a href="{{ route(($replyUser->hasRole('client') ? 'clients.show' : 'employees.show'), $replyUser->id) }}">
                        <h4 class="card-title f-15 f-w-500 text-dark mr-3">{{ $replyUser->name }}</h4>
                    </a>
                    <p class="card-date f-11 text-lightest mb-0 mr-3">
                        {{ $message->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                    </p>
                    <div class="dropdown ml-auto">
                        <button class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">

                            <a class="dropdown-item add-reply" data-row-id="{{ $message->id }}"
                                data-discussion-id="{{ $discussion->id }}" href="javascript:;">@lang('app.reply')</a>

                            @if ($key != 0 && is_null($discussion->best_answer_id) && $discussion->user_id == $replyUser->id)
                                <a class="dropdown-item set-best-answer" data-row-id="{{ $message->id }}"
                                    href="javascript:;">@lang('modules.discussions.bestReply')</a>
                            @endif

                            @if ($replyUser->id == user()->id)
                                <a class="dropdown-item edit-reply" data-row-id="{{ $message->id }}"
                                    data-discussion-id="{{ $discussion->id }}"
                                    href="javascript:;">@lang('app.edit')</a>
                            @endif
                            @if ($replyUser->id == user()->id || in_array('admin', $userRoles))
                                @if ($key != 0)
                                    <a class="dropdown-item delete-message" data-row-id="{{ $message->id }}"
                                        href="javascript:;">@lang('app.delete')</a>
                                @endif
                            @endif
                        </div>
                    </div>

                </div>
                <div class="card-text text-dark-grey text-justify ql-editor p-0">{!! $message->body !!}</div>

                @if ($discussion->best_answer_id == $message->id)
                    <span class="badge badge-success">@lang('modules.discussions.bestReply')</span>
                @endif
            </div>

        </div>
            <!-- TICKET MESSAGE START -->
            <div class="ticket-msg border-right-grey mt-3 ml-3" data-menu-vertical="1" data-menu-scroll="1"
            data-menu-dropdown-timeout="500" id="ticketMsg">
                <div class="d-flex flex-wrap mt-3 mb-2">
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

                                        @if ($file->icon == 'images')
                                            <a class="img-lightbox cursor-pointer d-block text-dark-grey f-13 pt-3 px-3" data-image-url="{{ $file->file_url }}" href="javascript:;">@lang('app.view')</a>
                                        @else
                                            <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 " target="_blank" href="{{ $file->file_url }}">@lang('app.view')</a>
                                        @endif


                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                            href="{{ route('discussion_file.download', md5($file->id)) }}">@lang('app.download')</a>

                                        @if (user()->id == $user->id)
                                            <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-file"
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
            <!-- TICKET MESSAGE END -->
    </div><!-- card end -->

@endforeach

@php
    $discussionId = $discussion->id;
@endphp
<div class="col-md-12 border-top border-right mb-5 bg-white" id="reply-section">
    <x-form id="replyDiscussion" method="POST" class="ajax-form">
        <input type="hidden" name="discussion_id" value="{{ $discussionId }}">
            <div class="col-md-12">
                <div class="form-group my-3">
                    <x-forms.label fieldReuired="true" fieldId="description" :fieldLabel="__('app.reply')">
                    </x-forms.label>
                    <div id="reply"></div>

                    <textarea name="description" id="description-text" class="d-none"></textarea>
                </div>
            </div>
            <input type="hidden" name= "discussion_type" value="discussion_reply">

            <div class="col-md-12">
                <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                    :fieldLabel="__('app.menu.addFile')" fieldName="file"
                    fieldId="file-upload-dropzone" />
                    <x-forms.button-primary  class="mb-2" id="save-discussion" icon="check">@lang('app.reply')</x-forms.button-primary>
            </div>
    </x-form>
</div>

<script>

    $(document).ready(function () {
      const atValues = @json($userData);
      quillMention(atValues, '#reply');
    });

    $('body').on('click', '.delete-file', function() {
        const id = $(this).data('row-id');
        const discussionFile = $(this);
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
                let url = "{{ route('discussion-files.destroy', ':id') }}";
                url = url.replace(':id', id);

                const token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            discussionFile.closest('.card').remove();
                        }
                    }
                });
            }
        });
    });

    Dropzone.autoDiscover = false;
        //Dropzone class
        taskDropzone = new Dropzone("#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('discussion-files.store') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: DROPZONE_MAX_FILES,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: DROPZONE_MAX_FILES,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function () {
                taskDropzone = this;
            }
        });
        //
        taskDropzone.on('sending', function (file, xhr, formData) {
            formData.append('discussion_id', {{$discussionId}});
            formData.append('discussion_reply_id', discussion_reply_id);
            $.easyBlockUI();
        });
        taskDropzone.on('uploadprogress', function () {
            $.easyBlockUI();
        });
        taskDropzone.on('removedfile', function () {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        taskDropzone.on('error', function (file, message) {
            taskDropzone.removeFile(file);
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).find(".help-block").remove();
            var helpBlockContainer = $(grp);

            if (helpBlockContainer.length == 0) {
                helpBlockContainer = $(grp);
            }

            helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
            $(grp).addClass("has-error");
            $(label).addClass("is-invalid");

        });
        taskDropzone.on('completemultiple', function () {
            $.easyAjax({
                url: '{{ route('discussion-reply.get_replies', $discussionId) }}',
                type: 'GET',
                success: function (response) {
                    if(response.status == 'success'){
                        $('#right-modal-content').html(response.html);
                        $(MODAL_XL).modal('hide');
                        $.easyUnblockUI();
                    }
                }
            });
        });


    $('#save-discussion').click(function() {
            const note = document.getElementById('reply').children[0].innerHTML;
            document.getElementById('description-text').value = note;
            var mentionUser = $('#reply span[data-id]').map(function(){
                            return $(this).attr('data-id')
                        }).get();

            var mention_user_id  =  $.makeArray(mentionUser);
            var discussionData = $('#replyDiscussion').serialize();

            var data = discussionData+='&mention_user_id=' + mention_user_id;

            $.easyAjax({
                url: "{{ route('discussion-reply.store') }}",
                container: '#replyDiscussion',
                type: "POST",
                blockUI: true,
                data: data,
                success: function(response) {
                    if (response.status == "success") {
                        if (taskDropzone.getQueuedFiles().length > 0) {
                            discussion_reply_id = response.discussion_reply_id;
                            taskDropzone.processQueue();
                        } else {
                            $('#right-modal-content').html(response.html);
                            $(MODAL_XL).modal('hide');
                        }
                    }
                }
            })
        });
</script>
