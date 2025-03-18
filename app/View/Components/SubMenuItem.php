<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SubMenuItem extends Component
{

    public $text;
    public $link;
    public $permission;
    public $addon;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($text, $link, $permission = true, $addon = false)
    {
        $this->text = $text;
        $this->link = $link;
        $this->addon = $addon;
        // Show icon only when permission is true
        $this->permission = $permission;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.sub-menu-item');
    }

}
