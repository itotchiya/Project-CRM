<?php

namespace App\Observers;

use App\Models\ContractSign;

class ContractSignObserver
{

    public function creating(ContractSign $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
