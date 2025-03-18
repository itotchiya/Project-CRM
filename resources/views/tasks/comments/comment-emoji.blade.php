<button class="btn cursor-pointer comment-like mr-2 f-12 btn-sm" data-comment-id="{{ $comment->id }}" data-emoji="thumbs-up" data-toggle="tooltip"
    @if($comment->like->count() != 0) data-original-title="{{ trans('modules.tasks.likeUser', [ 'user' => $allLikeUsers ]) }}" style="background-color: #f7f2f2;" @else data-original-title="@lang('modules.tasks.like')" @endif>
    <i class="fa fa-thumbs-up" ></i> {{$comment->like->count()}}</button>

<button class="btn cursor-pointer comment-like f-12 btn-sm" data-comment-id="{{ $comment->id }}" data-emoji="thumbs-down" data-toggle="tooltip"
    @if($comment->dislike->count() != 0) data-original-title="{{ trans('modules.tasks.dislikeUser', [ 'user' => $allDislikeUsers ]) }}" style="background-color: #f7f2f2;" @else data-original-title="@lang('modules.tasks.dislike')" @endif>
    <i class="fa fa-thumbs-down"></i> {{$comment->dislike->count()}}</button>
