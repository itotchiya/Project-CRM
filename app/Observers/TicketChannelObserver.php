<?php

namespace App\Observers;

use App\Models\TicketChannel;

class TicketChannelObserver
{

    public function creating(TicketChannel $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
