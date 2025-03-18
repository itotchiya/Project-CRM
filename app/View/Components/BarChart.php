<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BarChart extends Component
{

    public $chartData;
    public $multiple;
    public $spaceRatio;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($chartData, $multiple = false, $spaceRatio = '0.2')
    {
        $this->chartData = $chartData;
        $this->multiple = $multiple;
        $this->spaceRatio = $spaceRatio;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.bar-chart');
    }

}
