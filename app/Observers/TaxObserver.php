<?php

namespace App\Observers;

use App\Models\Tax;

class TaxObserver
{

    public function creating(Tax $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
