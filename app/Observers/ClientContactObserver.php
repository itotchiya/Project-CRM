<?php

namespace App\Observers;

use App\Models\ClientContact;

class ClientContactObserver
{

    public function saving(ClientContact $model)
    {
        if (user()) {
            $model->last_updated_by = user()->id;
        }
    }

    public function creating(ClientContact $model)
    {
        if (user()) {
            $model->added_by = user()->id;
        }

        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
