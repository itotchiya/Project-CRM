<?php

namespace App\Observers;

use App\Models\LeaveSetting;

class LeaveSettingObserver
{

    public function creating(LeaveSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
