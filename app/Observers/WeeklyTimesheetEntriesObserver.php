<?php

namespace App\Observers;

use App\Models\WeeklyTimesheetEntries;

class WeeklyTimesheetEntriesObserver
{
    public function creating(WeeklyTimesheetEntries $weeklyTimesheetEntries)
    {
        if (company()) {
            $weeklyTimesheetEntries->company_id = company()->id;
        }
    }
}
