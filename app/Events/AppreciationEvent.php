<?php

namespace App\Events;

use App\Models\Appreciation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppreciationEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userAppreciation;
    public $notifyUser;

    public function __construct(Appreciation $userAppreciation, $notifyUser)
    {
        $this->userAppreciation = $userAppreciation;
        $this->notifyUser = $notifyUser;
    }

}
