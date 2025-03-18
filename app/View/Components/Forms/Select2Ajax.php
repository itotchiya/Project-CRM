<?php

namespace App\View\Components\Forms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Select2Ajax extends Component
{


    public $multiple;
    public $search;
    public $alignRight;
    public $fieldLabel;
    public $fieldRequired;
    public $fieldName;
    public $fieldId;
    public $popover;
    public $format;
    public $route;
    public $placeholder;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $fieldName,
        $fieldId,
        $fieldRequired = false,
        $fieldLabel = null,
        bool $multiple = false,
        bool $search = false,
        bool $alignRight = false,
        $popover = null,
        $format = false,
        $route = null,
        $placeholder = null,
    )
    {
        $this->fieldName = $fieldName;
        $this->fieldLabel = $fieldLabel;
        $this->fieldId = $fieldId;
        $this->fieldRequired = $fieldRequired;
        $this->multiple = $multiple;
        $this->search = $search;
        $this->popover = $popover;
        $this->alignRight = $alignRight;
        $this->format = $format;
        $this->route = $route;
        $this->placeholder = $placeholder;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.forms.select2-ajax');
    }

}
