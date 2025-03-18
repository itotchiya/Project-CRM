<?php

namespace App\Observers;

use App\Models\DiscussionFile;

class DiscussionFileObserver
{

    public function creating(DiscussionFile $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
