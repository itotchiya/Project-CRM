<?php

namespace App\Listeners;

use App\Events\NewProposalEvent;
use App\Models\User;
use App\Notifications\NewProposal;
use App\Notifications\ProposalSigned;
use Illuminate\Support\Facades\Notification;

class NewProposalListener
{

    /**
     * @param NewProposalEvent $event
     */

    public function handle(NewProposalEvent $event)
    {
        if ($event->type == 'signed' && $event->proposal->status != 'waiting') {
            $allAdmins = User::allAdmins($event->proposal->company->id);
            // Notify admins
            Notification::send($allAdmins, new ProposalSigned($event->proposal));
        }
        else {
            // Notify client
            Notification::send($event->proposal->lead, new NewProposal($event->proposal));
        }
    }

}
