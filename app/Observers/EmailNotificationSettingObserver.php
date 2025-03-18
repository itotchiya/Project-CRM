<?php

namespace App\Observers;

use App\Models\EmailNotificationSetting;

class EmailNotificationSettingObserver
{

    public function creating(EmailNotificationSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
