<?php

namespace App\Observers;

use App\Models\TicketGroup;

class TicketGroupObserver
{

    public function creating(TicketGroup $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
