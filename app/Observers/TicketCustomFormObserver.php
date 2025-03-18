<?php

namespace App\Observers;

use App\Models\TicketCustomForm;

class TicketCustomFormObserver
{

    public function creating(TicketCustomForm $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
