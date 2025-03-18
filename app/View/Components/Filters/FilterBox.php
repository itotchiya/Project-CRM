<?php

namespace App\View\Components\Filters;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FilterBox extends Component
{

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.filters.filter-box');
    }

}
