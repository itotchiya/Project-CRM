<?php

namespace App\Listeners;

use App\Events\TaskNoteMentionEvent;
use App\Models\User;
use App\Notifications\TaskNoteMention;
use Illuminate\Support\Facades\Notification;

class TaskNoteMentionListener
{

    /**
     * Handle the event.
     *
     * @param TaskNoteMentionEvent $event
     * @return void
     */

    public function handle(TaskNoteMentionEvent $event)
    {
        if (isset($event->mentionuser)) {

            $mentionUserId = $event->mentionuser;
            $mentionUser = User::whereIn('id', ($mentionUserId))->get();
            Notification::send($mentionUser, new TaskNoteMention($event->task, $event));


        }

    }

}
