<?php

namespace App\Events;

use App\Models\Deal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $notifyUser;
    public $notificationName;

    public $deal;

    public function __construct(Deal $deal, $notifyUser, $notificationName)
    {
        $this->deal = $deal;
        $this->notifyUser = $notifyUser;
        $this->notificationName = $notificationName;
    }

}
