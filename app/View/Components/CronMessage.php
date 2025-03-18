<?php

namespace App\View\Components;

use App\Models\GlobalSetting;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CronMessage extends Component
{


    /**
     * @var false|mixed
     */
    private mixed $modal;

    public function __construct($modal = false)
    {
        $this->modal = $modal;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        $globalSetting = GlobalSetting::select(['id', 'hide_cron_message', 'last_cron_run'])->first();

        $modal = $this->modal;

        return view('components.cron-message', compact('globalSetting', 'modal'));
    }

}
