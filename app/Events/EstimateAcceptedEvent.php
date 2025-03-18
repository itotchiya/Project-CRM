<?php

namespace App\Events;

use App\Models\Estimate;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class EstimateAcceptedEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $estimate;

    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate;
    }

}
