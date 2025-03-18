<?php

namespace App\Observers;

use App\Models\EmployeeSkill;

class EmployeeSkillObserver
{

    public function creating(EmployeeSkill $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

}
