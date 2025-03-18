<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketRequesterEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $notifyUser;
    public $mentionUser;

    public function __construct(Ticket $ticket, $mentionUser = null, $notifyUser = null)
    {
        $this->ticket = $ticket;
        $this->notifyUser = $notifyUser;
        $this->mentionUser = $mentionUser;

    }

}
