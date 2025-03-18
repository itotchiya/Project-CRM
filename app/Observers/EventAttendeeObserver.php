<?php

namespace App\Observers;

use App\Models\EventAttendee;

class EventAttendeeObserver
{

    public function creating(EventAttendee $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

}
