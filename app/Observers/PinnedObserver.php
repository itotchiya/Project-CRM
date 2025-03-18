<?php

namespace App\Observers;

use App\Models\Pinned;

class PinnedObserver
{

    public function saving(Pinned $pinned)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (user()) {
            $pinned->user_id = user()->id;
        }
    }

    public function creating(Pinned $pinned)
    {
        if (company()) {
            $pinned->company_id = company()->id;
        }
    }

}
