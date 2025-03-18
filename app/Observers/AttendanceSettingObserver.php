<?php

namespace App\Observers;

use App\Models\AttendanceSetting;

class AttendanceSettingObserver
{

    public function creating(AttendanceSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
