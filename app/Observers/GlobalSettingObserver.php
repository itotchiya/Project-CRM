<?php

namespace App\Observers;

use App\Models\GlobalSetting;

class GlobalSettingObserver
{

    public function saving(GlobalSetting $model)
    {
        cache()->forget('global_setting');

        return $model;
    }

    public function updated(GlobalSetting $model)
    {
        cache()->forget('global_setting');

        return $model;
    }

    public function deleted(GlobalSetting $model)
    {
        cache()->forget('global_setting');

        return $model;
    }

}
