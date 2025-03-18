<?php

namespace App\Observers;

use App\Models\TicketEmailSetting;

class TicketEmailSettingObserver
{

    public function creating(TicketEmailSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
