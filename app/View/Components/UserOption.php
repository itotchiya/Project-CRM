<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserOption extends Component
{

    public $user;
    public $selected;
    public $pill;
    public $agent;
    public $userID;
    public $additionalText;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($user, $selected = false, $pill = false, $agent = false, $userID = null, $additionalText = null)
    {
        $this->user = $user;
        $this->selected = $selected;
        $this->pill = $pill;
        $this->agent = $agent;
        $this->userID = $userID;
        $this->additionalText = $additionalText;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.user-option');
    }

}
