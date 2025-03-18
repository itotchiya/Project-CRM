<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SettingSidebar extends Component
{

    public $activeMenu;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($activeMenu)
    {
        $this->activeMenu = $activeMenu;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.setting-sidebar');
    }

}
