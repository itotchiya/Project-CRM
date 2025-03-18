<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskNoteMentionEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $created_at;
    public $mentionuser;

    public function __construct(Task $task, $created_at, $mentionuser)
    {
        $this->task = $task;
        $this->created_at = $created_at;
        $this->mentionuser = $mentionuser;

    }

}
