<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FileCard extends Component
{

    public $fileName;
    public $dateAdded;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($fileName, $dateAdded)
    {
        $this->fileName = $fileName;
        $this->dateAdded = $dateAdded;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.file-card');
    }

}
