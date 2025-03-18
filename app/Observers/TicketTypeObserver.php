<?php

namespace App\Observers;

use App\Models\TicketType;

class TicketTypeObserver
{

    public function creating(TicketType $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
