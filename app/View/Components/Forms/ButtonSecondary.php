<?php

namespace App\View\Components\Forms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ButtonSecondary extends Component
{

    public $icon;
    public $disabled;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($icon = null, $disabled = false)
    {
        $this->icon = $icon;
        $this->disabled = $disabled;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.forms.button-secondary');
    }

}
