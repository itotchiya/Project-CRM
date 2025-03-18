<?php

namespace App\Observers;

use App\Models\AcceptEstimate;

class AcceptEstimateObserver
{

    public function creating(AcceptEstimate $model)
    {
        $model->company_id = $model->estimate->company_id;
    }

}
