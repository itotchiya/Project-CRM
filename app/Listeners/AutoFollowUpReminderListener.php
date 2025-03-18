<?php

namespace App\Listeners;

use App\Events\AutoFollowUpReminderEvent;
use App\Models\User;
use App\Notifications\AutoFollowUpReminder;
use Illuminate\Support\Facades\Notification;

class AutoFollowUpReminderListener
{

    /**
     * Handle the event.
     *
     * @param AutoFollowUpReminderEvent $event
     * @return void
     */

    public function handle(AutoFollowUpReminderEvent $event)
    {

        $companyId = $event->followup->lead->company_id;

        $adminUserIds = User::allAdmins($companyId)->pluck('id')->toArray();

        /** @phpstan-ignore-next-line */
        $notifyUser = (is_null($event->followup->lead->leadAgent)) ? User::whereIn('id', $adminUserIds)->get() : $event->followup->lead->leadAgent->user;

        if ($notifyUser) {
            Notification::send($notifyUser, new AutoFollowUpReminder($event->followup,$event->subject));
        }

    }

}
