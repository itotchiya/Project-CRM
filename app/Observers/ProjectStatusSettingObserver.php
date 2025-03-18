<?php

namespace App\Observers;

use App\Models\ProjectStatusSetting;

class ProjectStatusSettingObserver
{

    public function creating(ProjectStatusSetting $projectStatusSetting)
    {
        if (company()) {
            $projectStatusSetting->company_id = company()->id;
        }
    }

}
