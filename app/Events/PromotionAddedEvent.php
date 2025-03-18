<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PromotionAddedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $promotion;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct($promotion, $type = null)
    {
        $this->promotion = $promotion;
        $this->user = User::where('id', $promotion->employee_id)->first();
    }

}
