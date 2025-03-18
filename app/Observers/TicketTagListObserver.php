<?php

namespace App\Observers;

use App\Models\TicketTagList;

class TicketTagListObserver
{

    public function creating(TicketTagList $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
