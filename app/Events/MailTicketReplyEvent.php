<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MailTicketReplyEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticketReply;
    public $ticketEmailSetting;

    public function __construct($ticketReply, $ticketEmailSetting)
    {
        $this->ticketReply = $ticketReply;
        $this->ticketEmailSetting = $ticketEmailSetting;
    }

}
