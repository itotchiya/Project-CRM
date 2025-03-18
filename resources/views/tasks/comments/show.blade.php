@php
    $editTaskCommentPermission = user()->permission('edit_task_comments');
    $deleteTaskCommentPermission = user()->permission('delete_task_comments');
@endphp

@forelse ($comments as $comment)
    <div class="card w-100 rounded-1 border-2 mb-3 p-2 comment">
        <div class="card-horizontal">
            <div class="card-img my-1 ml-0 mx-1">
                <img src="{{ $comment->user->image_url }}" alt="{{ $comment->user->name }}">
            </div>
            <div class="card-body border-0 pl-0 py-1 ml-3">
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
                <div class="card-text f-14 text-dark-grey">
                    <div class="card-text f-14 text-dark-grey text-justify">
                        {!! $comment->comment !!}
                    </div>
                    <div id="emoji-{{$comment->id}}">
                        <button class="btn cursor-pointer comment-like mr-2 f-12 btn-sm" data-toggle="tooltip" data-comment-id="{{ $comment->id }}"
                            data-emoji="thumbs-up" @if($comment->like->count() != 0) data-original-title="{{ trans('modules.tasks.likeUser', [ 'user' => $likeUserList ]) }}" style="background-color: #f7f2f2;" @else data-original-title="@lang('modules.tasks.like')" @endif>
                            <i class="fa fa-thumbs-up"></i> {{ $comment->like->count() }}</button>
                        <button class="btn cursor-pointer comment-like f-12 btn-sm" data-toggle="tooltip" data-comment-id="{{ $comment->id }}"
                            data-emoji="thumbs-down" @if($comment->dislike->count() != 0) data-original-title="{{ trans('modules.tasks.dislikeUser', [ 'user' => $dislikeUserList ]) }}" style="background-color: #f7f2f2;" @else data-original-title="@lang('modules.tasks.dislike')" @endif>
                            <i class="fa fa-thumbs-down"></i> {{ $comment->dislike->count()}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <x-cards.no-record :message="__('messages.noCommentFound')" icon="comment-alt" />
@endforelse
