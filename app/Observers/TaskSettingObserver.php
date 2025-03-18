<?php

namespace App\Observers;

use App\Models\TaskSetting;

class TaskSettingObserver
{

    public function creating(TaskSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
