<?php

namespace App\Listeners;

use App\Events\TicketReplyEvent;
use App\Notifications\NewTicketReply;
use App\Models\User;
use App\Notifications\NewTicketNote;
use Illuminate\Support\Facades\Notification;

class TicketReplyListener
{

    /**
     * Handle the event.
     *
     * @param TicketReplyEvent $event
     * @return void
     */

    public function handle(TicketReplyEvent $event)
    {
        if ($event?->ticketReply?->type != 'note') {
            if (!is_null($event->notifyUser) && ($event->ticketReply->type != 'note')) {

                Notification::send($event->notifyUser, new NewTicketReply($event->ticketReply));
            }
            else {

                Notification::send(User::allAdmins($event->ticketReply->ticket->company->id), new NewTicketReply($event->ticketReply));
            }
        }

        if (!is_null($event->ticketReplyUsers)) {
            Notification::send($event->ticketReplyUsers, new NewTicketNote($event->ticketReply));
        }

    }

}
