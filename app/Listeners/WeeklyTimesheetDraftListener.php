<?php

namespace App\Listeners;

use App\Events\WeeklyTimesheetDraftEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\WeeklyTimesheetRejected;

class WeeklyTimesheetDraftListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WeeklyTimesheetDraftEvent $event): void
    {
        $submitBy = $event->weeklyTimesheet->user;

        if ($submitBy) {
            $submitBy->notify(new WeeklyTimesheetRejected($event->weeklyTimesheet));
        }
    }
}
