<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulkShiftEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $userData;
    public $dateRange;
    public $userId;

    public function __construct(User $userData, $dateRange, $userId)
    {
        $this->userData = $userData;
        $this->dateRange = $dateRange;
        $this->userId = $userId;
    }

}
