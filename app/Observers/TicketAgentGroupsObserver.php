<?php

namespace App\Observers;

use App\Models\TicketAgentGroups;

class TicketAgentGroupsObserver
{

    public function creating(TicketAgentGroups $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    public function saving(TicketAgentGroups $model)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $model->last_updated_by = user()->id;
        }
    }

    public function updating(TicketAgentGroups $model)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $model->last_updated_by = user()->id;
        }
    }

}
