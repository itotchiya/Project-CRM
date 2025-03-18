<?php

namespace App\Observers;

use App\Models\PaymentGatewayCredentials;

class PaymentGatewayCredentialsObserver
{

    public function creating(PaymentGatewayCredentials $notification)
    {
        if (company()) {
            $notification->company_id = company()->id;
        }
    }

}
