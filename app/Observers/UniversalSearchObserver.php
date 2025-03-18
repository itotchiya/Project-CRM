<?php

namespace App\Observers;

use App\Models\UniversalSearch;

class UniversalSearchObserver
{

    public function creating(UniversalSearch $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
