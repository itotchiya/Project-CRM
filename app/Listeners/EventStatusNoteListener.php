<?php

namespace App\Listeners;

use App\Events\EventStatusNoteEvent;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\EventStatusNote;

class EventStatusNoteListener
{

    /**
     * Handle the event.
     */
    public function handle(EventStatusNoteEvent $event)
    {
        $notifyUsers = $event->notifyUser->filter(function ($user) use ($event) {
            return $user->id !== $event->event->host;
        });

        $host = User::find($event->event->host);

        Notification::send($notifyUsers, new EventStatusNote($event->event));

        if ($host) {
            Notification::send($host, new EventStatusNote($event->event));
        }
    }

}
