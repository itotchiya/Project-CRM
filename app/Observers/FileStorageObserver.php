<?php

namespace App\Observers;

use App\Models\FileStorage;

class FileStorageObserver
{

    public function creating(FileStorage $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
