<?php

namespace App\Observers;

use App\Models\CurrencyFormatSetting;

class CurrencyFormatSettingObserver
{

    public function creating(CurrencyFormatSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
