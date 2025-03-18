<?php

namespace App\Observers;

use App\Events\RemovalRequestAdminLeadEvent;
use App\Events\RemovalRequestApprovedRejectLeadEvent;
use App\Models\RemovalRequestLead;
use Exception;
use Illuminate\Support\Facades\Log;

class RemovalRequestLeadObserver
{

    public function created(RemovalRequestLead $removalRequestLead)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new RemovalRequestAdminLeadEvent($removalRequestLead));
        }
    }

    public function updated(RemovalRequestLead $removal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            try {
                if ($removal->lead) {
                    event(new RemovalRequestApprovedRejectLeadEvent($removal));
                }
            } catch (Exception $e) {
                Log::info($e);
            }
        }
    }

    public function creating(RemovalRequestLead $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
