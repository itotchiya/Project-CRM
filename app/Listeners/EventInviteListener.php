<?php

namespace App\Listeners;

use App\Events\EventInviteEvent;
use App\Models\User;
use App\Notifications\EventHostInvite;
use App\Notifications\EventInvite;
use Illuminate\Support\Facades\Notification;

class EventInviteListener
{

    /**
     * Handle the event.
     *
     * @param EventInviteEvent $event
     * @return void
     */

    public function handle(EventInviteEvent $event)
    {
        $host = User::find($event->event->host);

        Notification::send($event->notifyUser, new EventInvite($event->event));
        if ($host) {
            Notification::send($host, new EventHostInvite($event->event));
        }

    }

}
