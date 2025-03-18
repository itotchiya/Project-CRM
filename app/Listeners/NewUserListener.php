<?php

namespace App\Listeners;

use App\Events\NewUserEvent;
use App\Notifications\NewUser;
use Illuminate\Support\Facades\Notification;

class NewUserListener
{

    public function handle(NewUserEvent $event)
    {
        Notification::send($event->user, new NewUser($event->user, $event->password));
    }

}
