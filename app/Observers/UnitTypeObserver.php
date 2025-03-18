<?php

namespace App\Observers;

use App\Models\UnitType;

class UnitTypeObserver
{

    public function creating(UnitType $unitType)
    {
        if (company()) {
            $unitType->company_id = company()->id;
        }
    }

}
