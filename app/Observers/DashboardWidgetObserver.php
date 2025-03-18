<?php

namespace App\Observers;

use App\Models\DashboardWidget;

class DashboardWidgetObserver
{

    public function creating(DashboardWidget $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
