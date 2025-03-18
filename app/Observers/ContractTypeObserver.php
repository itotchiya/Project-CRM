<?php

namespace App\Observers;

use App\Models\ContractType;

class ContractTypeObserver
{

    public function creating(ContractType $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
