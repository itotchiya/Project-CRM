<?php

namespace App\Observers;

use App\Models\Award;
use App\Models\Notification;

class AwardObserver
{

    public function creating(Award $appreciation)
    {
        if (company()) {
            $appreciation->company_id = company()->id;
        }
    }

    public function deleting(Award $award)
    {
        foreach ($award->appreciations as $appreciations) {
            Notification::where('type', 'App\Notifications\NewAppreciation')
                ->whereNull('read_at')
                ->where(function ($q) use ($appreciations) {
                    $q->where('data', 'like', '{"id":' . $appreciations->id . ',%');
                })->delete();
        }

    }

}
