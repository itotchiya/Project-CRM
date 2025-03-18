<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewPaymentEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payment;
    public $notifyUsers;

    public function __construct(Payment $payment, $notifyUsers)
    {
        $this->payment = $payment;
        $this->notifyUsers = $notifyUsers;
    }

}
