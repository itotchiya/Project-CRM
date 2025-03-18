<?php

namespace App\Events;

use App\Models\TicketReply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplyEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticketReply;
    public $notifyUser;
    public $ticketReplyUsers;

    public function __construct(TicketReply $ticketReply, $notifyUser, $ticketReplyUsers)
    {
        $this->ticketReply = $ticketReply;
        $this->notifyUser = $notifyUser;
        $this->ticketReplyUsers = $ticketReplyUsers;
    }

}
