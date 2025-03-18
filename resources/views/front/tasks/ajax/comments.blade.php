<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">

    <div class="d-flex flex-wrap justify-content-between p-20" id="comment-list">
        @forelse ($task->comments as $comment)
            <div class="card w-100 rounded-0 border-0 comment">
                <div class="card-horizontal">
                    <div class="card-img my-1 ml-0">
                        <img src="{{ $comment->user->image_url }}" alt="{{ $comment->user->name }}">
                    </div>
                    <div class="card-body border-0 pl-0 py-1">
                        <div class="d-flex flex-grow-1">
                            <h4 class="card-title f-15 f-w-500 text-dark mr-3">{{ $comment->user->name }}</h4>
                            <p class="card-date f-11 text-lightest mb-0">
                                {{ $comment->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="card-text f-14 text-dark-grey text-justify ql-editor">{!! $comment->comment !!}
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
