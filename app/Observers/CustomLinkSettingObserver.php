<?php

namespace App\Observers;

use App\Models\CustomLinkSetting;

class CustomLinkSettingObserver
{

    public function creating(CustomLinkSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
