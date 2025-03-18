<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstimateRequestAcceptedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $estimateRequest;

    /**
     * Create a new event instance.
     */
    public function __construct($estimateRequest)
    {
        $this->estimateRequest = $estimateRequest;
    }

}
