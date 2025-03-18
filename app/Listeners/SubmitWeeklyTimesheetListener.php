<?php

namespace App\Listeners;

use App\Events\SubmitWeeklyTimesheet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\NewTimesheetApproval;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class SubmitWeeklyTimesheetListener
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
    public function handle(SubmitWeeklyTimesheet $event): void
    {
        $admins = User::allAdmins();
        $reportingManager = $event->weeklyTimesheet->user->employeeDetails->reportingTo;

        Notification::send($admins, new NewTimesheetApproval($event->weeklyTimesheet));

        if ($reportingManager) {
            $reportingManager->notify(new NewTimesheetApproval($event->weeklyTimesheet));
        }
    }
}
