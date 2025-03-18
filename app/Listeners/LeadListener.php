<?php

namespace App\Listeners;

use App\Events\LeadEvent;
use App\Models\User;
use App\Notifications\NewLeadCreated;
use Illuminate\Support\Facades\Notification;

class LeadListener
{

    /**
     * Handle the event.
     *
     * @param LeadEvent $event
     * @return void
     */

    public function handle(LeadEvent $event)
    {
        $admins = User::allAdmins($event->leadContact->company->id);

        if (session('is_imported') == false) {
            Notification::send($admins, new NewLeadCreated($event->leadContact));
        }

    }

}
