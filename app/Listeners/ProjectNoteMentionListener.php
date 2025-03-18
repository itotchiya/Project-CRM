<?php

namespace App\Listeners;

use App\Events\ProjectNoteMentionEvent;
use App\Models\User;
use App\Notifications\ProjectNoteMention;
use Illuminate\Support\Facades\Notification;

class ProjectNoteMentionListener
{

    /**
     * Handle the event.
     *
     * @param ProjectNoteMentionEvent $event
     * @return void
     */

    public function handle(ProjectNoteMentionEvent $event)
    {
        if (isset($event->mentionuser)) {

            $mentionUserId = $event->mentionuser;
            $mentionUser = User::whereIn('id', ($mentionUserId))->get();
            Notification::send($mentionUser, new ProjectNoteMention($event->project, $event));

        }


    }

}
