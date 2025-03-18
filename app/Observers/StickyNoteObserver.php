<?php

namespace App\Observers;

use App\Models\StickyNote;

class StickyNoteObserver
{

    public function creating(StickyNote $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
