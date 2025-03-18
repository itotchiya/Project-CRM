<?php

namespace App\Listeners;

use App\Events\TicketEvent;
use App\Notifications\NewTicket;
use App\Notifications\TicketAgent;
use App\Models\User;
use App\Models\TicketGroup;
use App\Notifications\MentionTicketAgent;
use Illuminate\Support\Facades\Notification;

class TicketListener
{

    /**
     * Handle the event.
     *
     * @param TicketEvent $event
     * @return void
     */

    public function handle(TicketEvent $event)
    {

        if ($event->notificationName == 'NewTicket') {
            $group = TicketGroup::with('enabledAgents', 'enabledAgents.user')
                        ->where('id', $event->ticket->group_id)
                        ->first();
            if ($group && count($group->enabledAgents) > 0) {
                $usersToNotify = [];
                foreach ($group->enabledAgents as $agent) {
                    $usersToNotify[] = $agent->user;
                }
                Notification::send($usersToNotify, new NewTicket($event->ticket));
            }
            Notification::send(User::allAdmins(), new NewTicket($event->ticket));
        }
        elseif ($event->notificationName == 'TicketAgent') {
            Notification::send($event->ticket->agent, new TicketAgent($event->ticket));

        }
        elseif ($event->notificationName == 'MentionTicketAgent') {
            Notification::send($event->mentionUser, new MentionTicketAgent($event->ticket));

        }
    }

}
