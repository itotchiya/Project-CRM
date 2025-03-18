<?php

namespace App\Observers;

use App\Models\ProjectSetting;

class ProjectSettingObserver
{

    public function creating(ProjectSetting $projectSetting)
    {
        if (company()) {
            $projectSetting->company_id = company()->id;
        }
    }

}
