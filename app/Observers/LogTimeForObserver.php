<?php

namespace App\Observers;

use App\Models\LogTimeFor;

class LogTimeForObserver
{

    public function creating(LogTimeFor $logTimeFor)
    {
        if (company()) {
            $logTimeFor->company_id = company()->id;
        }
    }

}
