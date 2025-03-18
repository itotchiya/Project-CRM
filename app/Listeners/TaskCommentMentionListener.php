<?php

namespace App\Listeners;

use App\Events\TaskCommentMentionEvent;
use App\Models\User;
use App\Notifications\TaskCommentMention;
use Illuminate\Support\Facades\Notification;

class TaskCommentMentionListener
{

    /**
     * Handle the event.
     *
     * @param TaskCommentMentionEvent $event
     * @return void
     */

    public function handle(TaskCommentMentionEvent $event)
    {
        if (isset($event->mentionuser)) {

            $mentionUserId = $event->mentionuser;
            $mentionUser = User::whereIn('id', ($mentionUserId))->get();
            Notification::send($mentionUser, new TaskCommentMention($event->task, $event->comment));

        }

    }

}
