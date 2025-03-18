<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BirthdayReminderEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $upcomingBirthdays;
    public $company;

    public function __construct($company, $upcomingBirthdays)
    {
        $this->upcomingBirthdays = $upcomingBirthdays;
        $this->company = $company;
    }

}
