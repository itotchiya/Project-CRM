<?php

namespace App\Listeners;

use App\Events\EstimateRequestRejectedEvent;
use App\Notifications\EstimateRequestRejected;
use Illuminate\Support\Facades\Notification;

class EstimateRequestRejectedListener
{

    /**
     * Handle the event.
     */
    public function handle(EstimateRequestRejectedEvent $event): void
    {
        $company = $event->estimateRequest->company;
        $notifiable = $event->estimateRequest->client;

        if (isset($notifiable->email)){
            Notification::send($notifiable, new EstimateRequestRejected($event->estimateRequest));
        }
    }

}
