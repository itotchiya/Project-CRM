<?php

namespace App\Observers;

use App\Events\AppreciationEvent;
use App\Models\Appreciation;
use App\Models\Notification;

class AppreciationObserver
{

    public function creating(Appreciation $userAppreciation)
    {
        if (company()) {
            $userAppreciation->company_id = company()->id;
        }
    }

    public function created(Appreciation $userAppreciation)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new AppreciationEvent($userAppreciation, $userAppreciation->awardTo));
        }
    }

    public function deleting(Appreciation $appreciation)
    {
        Notification::where('type', 'App\Notifications\NewAppreciation')
            ->whereNull('read_at')
            ->where(function ($q) use ($appreciation) {
                $q->where('data', 'like', '{"id":' . $appreciation->id . ',%');
            })->delete();
    }

}
