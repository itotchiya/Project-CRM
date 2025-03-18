<?php

namespace App\Listeners;

use App\Events\AppreciationEvent;
use App\Notifications\NewAppreciation;
use Illuminate\Support\Facades\Notification;

class AppreciationListener
{

    /**
     * Handle the event.
     *
     * @param AppreciationEvent $event
     * @return void
     */

    public function handle(AppreciationEvent $event)
    {
        Notification::send($event->notifyUser, new NewAppreciation($event->userAppreciation));
    }

}
