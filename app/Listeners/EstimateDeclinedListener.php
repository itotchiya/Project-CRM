<?php

namespace App\Listeners;

use App\Events\EstimateDeclinedEvent;
use App\Notifications\EstimateDeclined;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class EstimateDeclinedListener
{

    /**
     * Handle the event.
     *
     * @param EstimateDeclinedEvent $event
     * @return void
     */

    public function handle(EstimateDeclinedEvent $event)
    {
        $company = $event->estimate->company;
        Notification::send(User::allAdmins($company->id), new EstimateDeclined($event->estimate));
    }

}
