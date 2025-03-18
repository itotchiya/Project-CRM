<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailyScheduleEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userData;
    public $notifiable;

    public function __construct($userData)
    {
        $this->userData = $userData;
    }

}
