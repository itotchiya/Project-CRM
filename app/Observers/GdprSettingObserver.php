<?php

namespace App\Observers;

use App\Models\GdprSetting;

class GdprSettingObserver
{

    //phpcs:ignore
    public function creating(GdprSetting $doc)
    {
        //
    }

}
