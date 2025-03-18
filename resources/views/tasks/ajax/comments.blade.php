@php
    $addTaskCommentPermission = user()->permission('add_task_comments');
    $editTaskCommentPermission = user()->permission('edit_task_comments');
    $deleteTaskCommentPermission = user()->permission('delete_task_comments');
@endphp

<style>
    .comment-like {
      background-color: #ffffff;
      border-radius:4px;
      border: 1px solid #99a5b5;
      color: #616e80;
      /* font-size: 14px; */
    }

    /* Darker background on mouse-over */
    .comment-like:hover {
      background-color: #f2f4f7;
      /* box-shadow: inset 0 0 0 1px #6d6a69, 0 2px 2px 0 rgb(0 0 0 / 8%); */
      color: #616e80;
    }

    .comment-time{
        position: relative;
        margin-top: 2px;
    }
    .ql-mention-list {
    list-style: none;
    margin: 0;
    padding: 0;
    overflow: hidden;
    }

    .ql-editor {
        text-align: left;
        /* white-space: unset; */
    }

    </style>
<!-- TAB CONTENT START -->

<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    @if ($addTaskCommentPermission == 'all'
    || ($addTaskCommentPermission == 'added' && $task->added_by == user()->id)
    || ($addTaskCommentPermission == 'owned' && in_array(user()->id, $taskUsers))
    || ($addTaskCommentPermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
    )
        <div class="row p-20">
            <div class="col-md-12">
                <a class="f-15 f-w-500" href="javascript:;" id="add-comment"><i
                        class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.contracts.addComment')
                </a>
            </div>
        </div>

        @php
            $userRoles = user_roles();
            $isAdmin = in_array('admin', $userRoles);
            $isEmployee = in_array('employee', $userRoles);
        @endphp

        @if ($task->approval_send == 1 && $task->project->need_approval_by_admin == 1 && $isEmployee && !$isAdmin && $status->slug == 'waiting_approval')
            <!-- Popup for Send Approval -->
            @include('tasks.ajax.sent-approval-modal')
        @else
            <x-form id="save-comment-data-form" class="d-none">
                <div class="col-md-12 p-20 ">
                    <div class="media">
                        <img src="{{ user()->image_url }}" class="align-self-start mr-3 taskEmployeeImg rounded"
                            alt="{{ user()->name }}">
                        <div class="media-body bg-white">
                            <div class="form-group">
                                <div id="task-comment"></div>
                                <textarea name="comment" class="form-control invisible d-none"
                                    id="task-comment-text"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="w-100 justify-content-end d-flex mt-2">
                        <x-forms.button-cancel id="cancel-comment" class="border-0 mr-3">@lang('app.cancel')
                        </x-forms.button-cancel>
                        <x-forms.button-primary id="submit-comment" icon="location-arrow">@lang('app.submit')
                            </x-forms.button-primary>
                    </div>



                </div>
            </x-form>
        @endif
    @endif
    <div class="d-flex flex-wrap justify-content-between p-20" id="comment-list">
        @forelse ($task->comments as $comment)
            <div class="card w-100 rounded-1 border-2 mb-3 p-2 comment">
                <div class="card-horizontal flex-row flex-wrap">
                    <div class="card-img m-1 ml-0 mr-3">
                        <img src="{{ $comment->user->image_url }}" alt="{{ $comment->user->name }}">
                    </div>
                    <div class="card-body border-0 pl-0 py-1">
                        <div class="row">
                            <div class="col-md-6 d-inline-flex">
                                <h4 class="card-title f-15 f-w-500 text-dark mr-3">{{ $comment->user->name }}</h4>
                                <span class="cursor-pointer card-date f-11 text-lightest mb-0 comment-time" data-toggle="tooltip"
                                data-original-title="{{ $comment->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}">
                                {{$comment->created_at->timezone(company()->timezone)->diffForHumans()}}
                                </span>
                            </div>
                            <div class="col-md-6 d-inline-flex justify-content-end">
                                @if ($editTaskCommentPermission == 'all' || ($editTaskCommentPermission == 'added' && $comment->added_by == user()->id))
                                    <a class="card-title cursor-pointer d-block text-dark-grey edit-comment mr-2"
                                        href="javascript:;" data-toggle="tooltip" data-original-title="@lang('app.edit')" data-row-id="{{ $comment->id }}"><i class="fa fa-edit mr-2"></i></a>
                                @endif
                                @if ($deleteTaskCommentPermission == 'all' || ($deleteTaskCommentPermission == 'added' && $comment->added_by == user()->id))
                                    <a class="cursor-pointer d-block text-dark-grey delete-comment"
                                        data-row-id="{{ $comment->id }}" data-toggle="tooltip"  href="javascript:;" data-original-title="@lang('app.delete')"><i class="fa fa-trash mr-2"></i></a>
                                @endif
                            </div>
                        </div>
                        @php
                            $likeUsers = $comment->likeUsers->pluck('name')->toArray();
                            $likeUserList = '';

                            if($likeUsers)
                            {
                                if(in_array(user()->name, $likeUsers)){
                                    $key = array_search(user()->name, $likeUsers);
                                    array_splice( $likeUsers, 0, 0, __('modules.tasks.you') );
                                    unset($likeUsers[$key+1]);

                                }
                                $likeUserList = implode(', ', $likeUsers);
                            }

                            $dislikeUsers = $comment->dislikeUsers->pluck('name')->toArray();
                            $dislikeUserList = '';

                            if($dislikeUsers)
                            {
                                if(in_array(user()->name, $dislikeUsers)){
                                    $key = array_search (user()->name, $dislikeUsers);
                                    array_splice( $dislikeUsers, 0, 0, __('modules.tasks.you') );
                                    unset($dislikeUsers[$key+1]);
                                }
                                $dislikeUserList = implode(', ', $dislikeUsers);
                            }
                        @endphp
                        <div class="card-text f-14 text-dark-grey ">
                            <div class="card-text f-14 text-dark-grey text-justify px-0">
                                {!! $comment->comment !!}

                            </div>
                            <div id="emoji-{{$comment->id}}">
                                <button class="btn cursor-pointer comment-like mr-2 f-12 btn-sm" data-comment-id="{{ $comment->id }}" data-toggle="tooltip"
                                    data-emoji="thumbs-up" @if($comment->like->count() != 0) data-original-title="{{ trans('modules.tasks.likeUser', [ 'user' => $likeUserList ]) }}" style="background-color: #f7f2f2;" @else data-original-title="@lang('modules.tasks.like')" @endif>
                                    <i class="fa fa-thumbs-up"></i> {{ $comment->like->count() }}</button>
                                <button class="btn cursor-pointer comment-like f-12 btn-sm" data-comment-id="{{ $comment->id }}" data-toggle="tooltip"
                                    data-emoji="thumbs-down" @if($comment->dislike->count() != 0) data-original-title="{{ trans('modules.tasks.dislikeUser', [ 'user' => $dislikeUserList ]) }}" style="background-color: #f7f2f2;" @else data-original-title="@lang('modules.tasks.dislike')" @endif>
                                    <i class="fa fa-thumbs-down"></i> {{ $comment->dislike->count()}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="align-items-center d-flex flex-column text-lightest p-20 w-100">
                <i class="fa fa-comment-alt f-21 w-100"></i>

                <div class="f-15 mt-4">
                    - @lang('messages.noCommentFound') -
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- TAB CONTENT END -->
<script>
    var add_task_comments = "{{ $addTaskCommentPermission }}";
    $(document).ready(function() {

        var send_approval = "{{ $task->approval_send }}";
        var admin = "{{ in_array('admin', user_roles()) }}";
        var employee = "{{ in_array('employee', user_roles()) }}";
        var needApproval = "{{ $task?->project?->need_approval_by_admin }}";
        var status = "{{ $status->slug }}";

        $('#add-comment').click(function() {
            if (send_approval == 1 && employee == 1 && admin != 1 && needApproval == 1 && status == 'waiting_approval') {
                $('#send-approval-modal').modal('show');
                $('.modal-backdrop').css('display', 'none');
            }else{
                $(this).closest('.row').addClass('d-none');
                $('#save-comment-data-form').removeClass('d-none');
            }
        });

    });

    $('#cancel-comment').click(function() {
        $('#save-comment-data-form').addClass('d-none');
        $('#add-comment').closest('.row').removeClass('d-none');

    });
        //quill mention

    var userValues = @json($taskuserData);

    $(document).ready(function() {
        if (add_task_comments == "all" || add_task_comments == "added") {
            quillMention(userValues, '#task-comment');
        }
        });


        $('#submit-comment').click(function() {
            var comment = document.getElementById('task-comment').children[0].innerHTML;
            document.getElementById('task-comment-text').value = comment;
            var mention_user_id = $('#task-comment span[data-id]').map(function(){
                return $(this).attr('data-id')

            }).get();

            var token = '{{ csrf_token() }}';
            const url = "{{ route('taskComment.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-comment-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#submit-comment",
                data: {
                    '_token': token,
                    comment: comment,
                    mention_user_id : mention_user_id,
                    taskId: '{{ $task->id }}'
                },
                success: function(response) {
                    if (response.status == "success") {
                        $('#comment-list').html(response.view);
                        document.getElementById('task-comment').children[0].innerHTML = "";
                        $('#task-comment-text').val('');
                    }

                }
            });
        });


  </script>
