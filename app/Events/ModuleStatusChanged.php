<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModuleStatusChanged
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $module;
    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct($module, $status)
    {
        $this->module = $module;
        $this->status = $status;
    }

}
