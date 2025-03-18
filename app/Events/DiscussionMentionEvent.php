<?php

namespace App\Events;

use App\Models\Discussion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscussionMentionEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $discussion;
    public $mentionuser;

    public function __construct(Discussion $discussion, $mentionuser)
    {

        $this->discussion = $discussion;
        $this->mentionuser = $mentionuser;

    }

}
