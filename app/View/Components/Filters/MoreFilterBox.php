<?php

namespace App\View\Components\Filters;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MoreFilterBox extends Component
{

    public $extraSlot;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($extraSlot = false)
    {
        $this->extraSlot = $extraSlot;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.filters.more-filter-box');
    }

}
