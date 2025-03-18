<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LeadCard extends Component
{

    public $lead;
    public $draggable;

    /**
     * Create a new component instance.
     *
     * @return void
     */

    public function __construct($lead, $draggable = 'true')
    {
        $this->lead = $lead;
        $this->draggable = $draggable;

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.lead-card');
    }

}
