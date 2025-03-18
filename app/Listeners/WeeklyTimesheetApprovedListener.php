<?php

namespace App\Listeners;

use App\Events\WeeklyTimesheetApprovedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\WeeklyTimesheetApproved;
class WeeklyTimesheetApprovedListener
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
    public function handle(WeeklyTimesheetApprovedEvent $event): void
    {
        $submitBy = $event->weeklyTimesheet->user;

        if ($submitBy) {
            $submitBy->notify(new WeeklyTimesheetApproved($event->weeklyTimesheet));
        }

    }
}
