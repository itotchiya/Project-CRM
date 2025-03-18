<?php

namespace App\Events;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCommentMentionEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $comment;
    public $mentionuser;

    public function __construct(Task $task, TaskComment $comment, $mentionuser)
    {
        $this->task = $task;
        $this->comment = $comment;
        $this->mentionuser = $mentionuser;
    }

}
