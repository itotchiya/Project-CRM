<?php

namespace App\Traits;

use App\Models\Payment;

trait MakePaymentTrait
{

    /**
     * makePayment to generate payment of invoice.
     *
     * @param string|null $gateway
     * @param int|float $amount
     * @param Invoice|Collection $invoice
     * @param array|int|string $transactionId This can be single transaction id or array of transaction ids
     * @param string $status (default: 'pending')
     * @return Payment $payment
     */
    public function makePayment($gateway, $amount, $invoice, $transactionId, $status = 'pending')
    {
        $payment = Payment::query();

        if (is_array($transactionId)) {
            $payment->whereIn('transaction_id', $transactionId)->orWhereIn('event_id', $transactionId);
        }
        else {
            $payment->where('transaction_id', $transactionId)->orWhere('event_id', $transactionId);
        }

        $payment = $payment->latest()->first();

        $payment = ($payment && !empty($transactionId)) ? $payment : new Payment();
        $payment->project_id = $invoice->project_id;
        $payment->invoice_id = $invoice->id;
        $payment->order_id = $invoice->order_id;
        $payment->gateway = $gateway;
        // If transactionId is array, then use the first one as transaction id
        $payment->transaction_id = is_array($transactionId) ? ($transactionId[0] ?? null) : $transactionId;
        $payment->event_id = is_array($transactionId) ? ($transactionId[0] ?? null) : $transactionId;
        $payment->currency_id = $invoice->currency_id;
        $payment->amount = $amount;
        $payment->paid_on = now();
        $payment->status = $status;
        $payment->save();

        return $payment;
    }

    public function getWebhook()
    {
        return response()->json(['message' => 'This URL should not be accessed directly. Only POST requests are allowed.']);
    }

}
