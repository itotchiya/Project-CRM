<?php

namespace App\Observers;

use App\Models\ExpensesCategoryRole;

class ExpensesCategoryRoleObserver
{

    public function creating(ExpensesCategoryRole $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

}
