<?php

namespace App\Observers;

use App\Models\EmployeeTeam;

class EmployeeTeamObserver
{

    public function creating(EmployeeTeam $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

}
