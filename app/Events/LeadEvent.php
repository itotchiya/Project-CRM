<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notificationName;
    /**
     * @var Lead
     */
    public $leadContact;

    public function __construct(Lead $leadContact, $notificationName)
    {
        $this->leadContact = $leadContact;
        $this->notificationName = $notificationName;
    }

}
