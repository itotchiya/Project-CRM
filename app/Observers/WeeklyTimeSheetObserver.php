<?php

namespace App\Observers;

use App\Models\WeeklyTimesheet;

class WeeklyTimeSheetObserver
{
    public function creating(WeeklyTimesheet $weeklyTimesheet)
    {
        if (company()) {
            $weeklyTimesheet->company_id = company()->id;
        }

    }

}
