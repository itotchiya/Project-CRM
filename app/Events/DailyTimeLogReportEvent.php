<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailyTimeLogReportEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $user;
    public $role;
    public $company;

    public function __construct(User $user, $role, $company)
    {
        $this->user = $user;
        $this->role = $role;
        $this->company = $company;
    }

}
