<?php

namespace App\Listeners;

use App\Events\NewUserSlackEvent;
use App\Notifications\NewUserSlack;
use Illuminate\Support\Facades\Notification;

class NewUserSlackListener
{

    public function handle(NewUserSlackEvent $event)
    {
        Notification::send($event->user, new NewUserSlack($event->user));
    }

}
