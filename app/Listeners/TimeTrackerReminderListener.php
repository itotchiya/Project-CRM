<?php

namespace App\Listeners;

use App\Events\TimeTrackerReminderEvent;
use App\Notifications\TimeTrackerReminder;
use Illuminate\Support\Facades\Notification;

class TimeTrackerReminderListener
{

    /**
     * Handle the event.
     *
     * @param TimeTrackerReminderEvent $event
     * @return void
     */

    public function handle(TimeTrackerReminderEvent $event)
    {
        Notification::send($event->user, new TimeTrackerReminder($event->user));
    }

}
