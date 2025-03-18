<?php

namespace App\Observers;

use App\Models\MessageSetting;

class MessageSettingObserver
{

    public function creating(MessageSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
