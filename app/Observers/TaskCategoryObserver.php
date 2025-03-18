<?php

namespace App\Observers;

use App\Models\TaskCategory;

class TaskCategoryObserver
{

    /**
     * @param TaskCategory $item
     */
    public function saving(TaskCategory $item)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $item->last_updated_by = user()->id;
        }
    }

    public function creating(TaskCategory $model)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $model->added_by = user()->id;
        }

        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
