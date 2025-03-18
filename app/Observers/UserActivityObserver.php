<?php

namespace App\Observers;

use App\Models\UserActivity;

class UserActivityObserver
{

    public function creating(UserActivity $model)
    {
        $model->company_id = $model->user->company_id;
    }

}
