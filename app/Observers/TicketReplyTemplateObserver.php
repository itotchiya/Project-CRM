<?php

namespace App\Observers;

use App\Models\TicketReplyTemplate;

class TicketReplyTemplateObserver
{

    public function creating(TicketReplyTemplate $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
