<?php

namespace App\Events;

use App\Models\EstimateRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstimateRequestRejectedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $estimateRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(EstimateRequest $estimateRequest)
    {
        $this->estimateRequest = $estimateRequest;
    }

}
