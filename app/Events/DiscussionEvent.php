<?php

namespace App\Events;

use App\Models\Discussion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscussionEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $discussion;
    public $project_member;

    public function __construct(Discussion $discussion, $project_member)
    {
        $this->discussion = $discussion;
        $this->project_member = $project_member;
    }

}
