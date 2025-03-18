<?php

namespace App\Listeners;

use App\Events\DailyScheduleEvent;
use App\Notifications\DailyScheduleNotification;
use Illuminate\Support\Facades\Notification;

class DailyScheduleListener
{
    /**
     * Create the event listener.
     */

    /**
     * Handle the event.
     */
    public function handle(DailyScheduleEvent $event)
    {
        foreach($event->userData as $key => $notifiable)
        {
            Notification::send($notifiable['user'], new DailyScheduleNotification($event->userData[$key]));
        }
    }

}
