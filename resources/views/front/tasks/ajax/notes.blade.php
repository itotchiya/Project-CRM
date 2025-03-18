<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    <div class="d-flex flex-wrap justify-content-between p-20" id="note-list">
        @forelse($task->notes as $note)
            <div class="card w-100 rounded-0 border-0 note">
                <div class="card-horizontal">
                    <div class="card-img my-1 ml-0">
                        <img src="{{ $note->user->image_url }}" alt="{{ $note->user->name }}">
                    </div>
                    <div class="card-body border-0 pl-0 py-1">
                        <div class="d-flex flex-grow-1">
                            <h4 class="card-title f-15 f-w-500 text-dark mr-3">{{ $note->user->name }}</h4>
                            <p class="card-date f-11 text-lightest mb-0">
                                {{ $note->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="card-text f-14 text-dark-grey text-justify ql-editor">{!! $note->note !!}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <x-cards.no-record :message="__('messages.noNoteFound')" icon="clipboard" />
        @endforelse
    </div>
</div>
<!-- TAB CONTENT END -->
