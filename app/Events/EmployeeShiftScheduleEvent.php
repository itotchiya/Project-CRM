<?php

namespace App\Events;

use App\Models\EmployeeShiftSchedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeShiftScheduleEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $employeeShiftSchedule;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        $this->employeeShiftSchedule = $employeeShiftSchedule;
    }

}
