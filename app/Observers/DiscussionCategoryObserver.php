<?php

namespace App\Observers;

use App\Models\DiscussionCategory;

class DiscussionCategoryObserver
{

    public function creating(DiscussionCategory $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
