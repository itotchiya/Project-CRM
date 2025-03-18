<?php

namespace App\Listeners;

use App\Events\DiscussionReplyEvent;
use App\Notifications\NewDiscussionReply;
use Illuminate\Support\Facades\Notification;

class DiscussionReplyListener
{

    /**
     * Handle the event.
     *
     * @param DiscussionReplyEvent $event
     * @return void
     */

    public function handle(DiscussionReplyEvent $event)
    {
        $client = $event->discussionReply?->discussion?->project?->client;

        if ($client) {
            Notification::send($client, new NewDiscussionReply($event->discussionReply));
        }

        Notification::send($event->notifyUser, new NewDiscussionReply($event->discussionReply));
    }

}
