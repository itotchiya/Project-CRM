<?php

namespace App\Observers;

use App\Models\ClientDetails;
use App\Traits\EmployeeActivityTrait;

class ClientDetailsObserver
{
    use EmployeeActivityTrait;

    /**
     * @param ClientDetails $model
     */
    public function created(ClientDetails $model)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'client-created', $model->user_id, 'client');
        }
    }

    public function saving(ClientDetails $model)
    {
        if (user()) {
            $model->last_updated_by = user()->id; 
        }

        if (request()->has('added_by')) {
            $model->added_by = request('added_by');
        }
    }

    public function creating(ClientDetails $model)
    {
        if (user()) {
            $model->added_by = user()->id;
        }

        if (request()->has('added_by')) {
            $model->added_by = request('added_by');
        }

        if(request()->has('is_client_contact')){
            $user = $model->user()->first();
            $model->company_id = $user->company_id;
        }else{
            $model->company_id = $model->user->company_id;
        }

    }

}
