<?php

namespace App\Observers;

use App\Models\Currency;

class CurrencyObserver
{

    public function creating(Currency $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
