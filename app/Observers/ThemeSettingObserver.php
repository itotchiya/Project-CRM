<?php

namespace App\Observers;

use App\Models\ThemeSetting;

class ThemeSettingObserver
{

    public function creating(ThemeSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
