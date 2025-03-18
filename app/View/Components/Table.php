<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{

    public $headType = '';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($headType = '')
    {
        $this->headType = $headType;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.table');
    }

}
