<?php

namespace App\Observers;

use App\Models\UserTaskboardSetting;

class UserTaskboardSettingObserver
{

    public function creating(UserTaskboardSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
