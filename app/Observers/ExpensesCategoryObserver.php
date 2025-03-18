<?php

namespace App\Observers;

use App\Models\ExpensesCategory;

class ExpensesCategoryObserver
{

    public function creating(ExpensesCategory $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

}
