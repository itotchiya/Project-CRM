<?php

namespace App\Observers;

use App\Models\NoticeView;

class NoticeViewObserver
{

    public function creating(NoticeView $noticeView)
    {
        if (company()) {
            $noticeView->company_id = company()->id;
        }
    }

}
