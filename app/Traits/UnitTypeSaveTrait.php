<?php

namespace App\Traits;

use App\Models\UnitType;

trait UnitTypeSaveTrait
{

    public function unitType($model)
    {
        /* If the unit_id is already set, there's nothing to do */
        if (!is_null($model->unit_id)) {
            return $model;
        }

        /* Find the first unit type for the company */
        $type = UnitType::where('company_id', $model->company_id)->first();

        /* If a unit type was found, set the unit_id and return the updated model */
        if ($type) {
            $model->unit_id = $type->id;
        }

        return $model;
    }

}
