<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectNoteMentionEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;
    public $mentionuser;
    public $created_at;

    public function __construct(Project $project, $created_at, $mentionuser)
    {

        $this->project = $project;
        $this->created_at = $created_at;
        $this->mentionuser = $mentionuser;

    }

}
