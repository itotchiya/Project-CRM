<?php

namespace App\Observers;

use App\Models\ClientUserNote;

class ClientUserNotesObserver
{

    public function creating(ClientUserNote $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
