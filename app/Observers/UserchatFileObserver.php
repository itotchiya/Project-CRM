<?php

namespace App\Observers;

use App\Models\UserchatFile;

class UserchatFileObserver
{

    public function creating(UserchatFile $model)
    {
        $model->company_id = $model->chat->company_id;
    }

}
