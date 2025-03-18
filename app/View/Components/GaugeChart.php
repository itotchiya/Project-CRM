<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GaugeChart extends Component
{

    public $value;
    public $width;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($value, $width)
    {
        $this->value = $value;
        $this->width = $width;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.gauge-chart');
    }

}
