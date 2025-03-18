<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkPayments extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [];

        $invoiceIds = request()->invoice_number;

        foreach ($invoiceIds as $index => $invoiceId) {

            $amount = request()->amount[$index];
            $transaction_id = request()->transaction_id[$index];
            $gateway = request()->gateway[$index];
            $offline_method_id = request()->offline_method_id[$index];
            $payment_date = request()->payment_date[$index];

            if ($gateway != 'all' && $gateway == 'Offline' && $offline_method_id == null) {
                $rules['payment_date.'.$index.''] = 'required';
                $rules['offline_methods.'.$index.''] = 'required';
                $rules['amount.'.$index.''] = 'required';
            }
            elseif ($gateway != 'all' && $gateway != 'Offline') {
                $rules['payment_date.'.$index.''] = 'required';
                $rules['amount.'.$index.''] = 'required';
            }

            if ($gateway != 'all' && $gateway == 'Offline') {
                $rules['amount.'.$index.''] = 'required';
            }

            if ($gateway == 'all' && (!is_null($amount) || $amount != null)) {
                $rules['gateway.'.$index.''] = 'required';
            }

            if (is_null($payment_date) || $payment_date == null) {
                $rules['payment_date.'.$index.''] = 'required';
            }

        }

        return $rules;
    }

    public function messages()
    {
        $message = [];

        $invoiceIds = request()->invoice_number;

        foreach ($invoiceIds as $index => $invoiceId) {

            $amount = request()->amount[$index];
            $transaction_id = request()->transaction_id[$index];
            $gateway = request()->gateway[$index];
            $offline_method_id = request()->offline_method_id[$index];
            $payment_date = request()->payment_date[$index];

            if ($gateway != 'all' && $gateway == 'Offline' && $offline_method_id == null)
            {
                $message['payment_date.'.$index.''] = __('messages.invoiceDateError');
                $message['offline_methods.'.$index.''] = __('messages.selectOfflineMethod');
                $message['amount.'.$index.''] = __('messages.invoicePaymentError');
            }
            elseif ($gateway != 'all' && $gateway != 'Offline') {
                $message['payment_date.'.$index.''] = __('messages.invoiceDateError');
                $message['amount.'.$index.''] = __('messages.invoicePaymentError');
            }

            if ($gateway != 'all' && $gateway == 'Offline') {
                $message['amount.'.$index.''] = __('messages.invoicePaymentError');
            }

            if ($gateway == 'all' && (!is_null($amount) || $amount != null)) {
                $message['gateway.'.$index.''] = __('messages.selectGateway');
            }

            if (is_null($payment_date) || $payment_date == null) {
                $message['payment_date.'.$index.''] = __('messages.invoiceDateError');
            }
        }

        return $message;
    }

}
