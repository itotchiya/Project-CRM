<?php

namespace App\Observers;

use App\Models\OfflinePaymentMethod;

class OfflinePaymentMethodObserver
{

    public function creating(OfflinePaymentMethod $notification)
    {
        if (company()) {
            $notification->company_id = company()->id;
        }
    }

}
