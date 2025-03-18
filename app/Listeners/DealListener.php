<?php

namespace App\Listeners;

use App\Events\DealEvent;
use App\Models\Deal;
use App\Notifications\DealStageUpdated;
use App\Notifications\LeadAgentAssigned;
use Illuminate\Support\Facades\Notification;

class DealListener
{

    /**
     * Handle the event.
     *
     * @param DealEvent $event
     * @return void
     */

    public function handle(DealEvent $event)
    {
        $lead = Deal::with('leadAgent', 'leadAgent.user')->findOrFail($event->deal->id);

        if ($event->notificationName == 'LeadAgentAssigned') {

            if ($lead->leadAgent) {
                Notification::send($lead->leadAgent->user, new LeadAgentAssigned($lead));
            }
        }

        if ($event->notificationName == 'StageUpdated') {

            if ($lead->leadAgent) {
                Notification::send($lead->leadAgent->user, new DealStageUpdated($lead));
            }
        }
    }

}
