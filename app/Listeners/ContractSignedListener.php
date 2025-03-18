<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\ContractSignedEvent;
use App\Notifications\ContractSigned;
use Illuminate\Support\Facades\Notification;

class ContractSignedListener
{

    /**
     * Handle the event.
     *
     * @param ContractSignedEvent $event
     * @return void
     */
    public function handle(ContractSignedEvent $event)
    {
        Notification::send(User::allAdmins($event->contract->company->id), new ContractSigned($event->contract, $event->contractSign));
    }

}
