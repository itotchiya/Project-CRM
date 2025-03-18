<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tab extends Component
{

    public $href;
    public $text;
    public $ajax;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($href, $text, $ajax = 'true')
    {
        $this->href = $href;
        $this->text = $text;
        $this->ajax = $ajax;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.tab');
    }

}
