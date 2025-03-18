<?php

namespace App\Listeners;

use App\Events\HolidayEvent;
use App\Notifications\NewHoliday;
use Illuminate\Support\Facades\Notification;

class HolidayListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param HolidayEvent $event
     * @return void
     */
    public function handle(HolidayEvent $event)
    {
        Notification::send($event->notifyUser, new NewHoliday($event->holiday));
    }

}
