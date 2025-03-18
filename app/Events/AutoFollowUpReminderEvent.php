<?php

namespace App\Events;

use App\Models\DealFollowUp;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AutoFollowUpReminderEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $followup;
    public $subject;

    public function __construct(DealFollowUp $followup, $subject)
    {
        $this->followup = $followup;
        $this->subject = $subject;
    }

}
