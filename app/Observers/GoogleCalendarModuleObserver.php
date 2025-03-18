<?php

namespace App\Observers;

use App\Models\GoogleCalendarModule;

class GoogleCalendarModuleObserver
{

    public function creating(GoogleCalendarModule $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

}
