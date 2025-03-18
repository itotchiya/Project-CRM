<?php

namespace App\Observers;

use App\Models\CustomFieldGroup;

class CustomFieldGroupObserver
{

    public function creating(CustomFieldGroup $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
