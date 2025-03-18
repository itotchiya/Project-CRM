<?php

namespace App\Observers;

use App\Models\TicketTag;

class TicketTagObserver
{

    public function creating(TicketTag $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
