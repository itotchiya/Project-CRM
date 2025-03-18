<?php

namespace App\Listeners;

use App\Events\DailyTimeLogReportEvent;
use App\Notifications\DailyTimeLogReport;
use Illuminate\Support\Facades\Notification;

class DailyTimeLogReportListener
{

    public function handle(DailyTimeLogReportEvent $event): void
    {
        Notification::send($event->user, new DailyTimeLogReport($event->user, $event->role));
    }

}
