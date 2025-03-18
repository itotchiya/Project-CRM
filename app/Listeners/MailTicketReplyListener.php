<?php

namespace App\Listeners;

use App\Events\MailTicketReplyEvent;
use App\Notifications\MailTicketReply;
use Illuminate\Support\Facades\Notification;

class MailTicketReplyListener
{

    public function handle(MailTicketReplyEvent $event)
    {
        if (!is_null($event->ticketReply->ticket->agent_id)) {
            if ($event->ticketReply->ticket->agent_id == $event->ticketReply->user_id) {
                Notification::send($event->ticketReply->ticket->client, new MailTicketReply($event->ticketReply, $event->ticketEmailSetting));
            }
            else {
                Notification::send($event->ticketReply->ticket->agent, new MailTicketReply($event->ticketReply, $event->ticketEmailSetting));
            }
        }
    }

}
