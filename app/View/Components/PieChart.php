<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PieChart extends Component
{

    public $labels;
    public $values;
    public $colors;
    public $fullscreen;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($labels, $values, $colors, $fullscreen = false)
    {
        $this->labels = $labels;
        $this->values = $values;
        $this->colors = $colors;
        $this->fullscreen = $fullscreen;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.pie-chart');
    }

}
