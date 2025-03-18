<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskCard extends Component
{

    public $task;
    public $draggable;
    public $company;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($task, $draggable = 'true', $company)
    {
        $this->task = $task;
        $this->draggable = $draggable;
        $this->company = $company;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.task-card');
    }

}
