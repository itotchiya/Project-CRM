<?php

namespace App\Observers;

use App\Events\EstimateRequestAcceptedEvent;
use App\Events\EstimateRequestRejectedEvent;
use App\Events\NewEstimateRequestEvent;
use App\Models\EstimateRequest;

class EstimateRequestObserver
{

    /**
     * Handle the EstimateRequest "created" event.
     */
    public function created(EstimateRequest $estimateRequest): void
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new NewEstimateRequestEvent($estimateRequest));
        }
    }

    /**
     * Handle the EstimateRequest "updated" event.
     */
    public function updated(EstimateRequest $estimateRequest): void
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($estimateRequest->status == 'rejected') {
                event(new EstimateRequestRejectedEvent($estimateRequest));
            }

            if ($estimateRequest->status == 'accepted') {
                event(new EstimateRequestAcceptedEvent($estimateRequest));
            }
        }
    }

    /**
     * Handle the EstimateRequest "deleted" event.
     */
    public function deleted(EstimateRequest $estimateRequest): void
    {
        //
    }

    /**
     * Handle the EstimateRequest "restored" event.
     */
    public function restored(EstimateRequest $estimateRequest): void
    {
        //
    }

    /**
     * Handle the EstimateRequest "force deleted" event.
     */
    public function forceDeleted(EstimateRequest $estimateRequest): void
    {
        //
    }

}
