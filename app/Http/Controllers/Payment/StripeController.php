<?php

namespace App\Http\Controllers\Payment;

use Stripe\Stripe;
use App\Helper\Reply;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Traits\MakePaymentTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\PaymentGatewayCredentials;

class StripeController extends Controller
{
    use MakePaymentTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $stripeCredentials = PaymentGatewayCredentials::first();

        /** setup Stripe credentials **/
        Stripe::setApiKey($stripeCredentials->stripe_mode == 'test' ? $stripeCredentials->test_stripe_secret : $stripeCredentials->live_stripe_secret);
        $this->pageTitle = __('app.stripe');
    }

    /**
     * Store a details of payment with paypal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function paymentWithStripe(Request $request, $id)
    {
        $redirectRoute = 'invoices.show';
        $invoice = Invoice::findOrFail($id);
        $param = 'invoice';
        $paymentIntentId = $request->paymentIntentId;

        if(isset($request->type) && $request->type == 'order'){
            $redirectRoute = 'orders.show';
            $param = 'order';
            $invoice = Invoice::where('order_id', $id)->latest()->first();
        }

        $this->makePayment('Stripe', $invoice->amountDue(), $invoice, $paymentIntentId, 'complete');
        $invoice->status = 'paid';
        $invoice->save();

        return $this->makeStripePayment($redirectRoute, $id, $param);
    }

    public function paymentWithStripePublic(Request $request, $hash)
    {
        $redirectRoute = 'front.invoice';
        $paymentIntentId = $request->paymentIntentId;

        $invoice = Invoice::where('hash', $hash)->first();

        $this->makePayment('Stripe', $invoice->amountDue(), $invoice, $paymentIntentId, 'complete');
        $invoice->status = 'paid';
        $invoice->save();
        return $this->makeStripePayment($redirectRoute, $hash, 'hash');
    }

    private function makeStripePayment($redirectRoute, $id , $param = null)
    {
        $param = $param ?? 'invoice';
        $signedUrl = url()->temporarySignedRoute($redirectRoute, now()->addDays(\App\Models\GlobalSetting::SIGNED_ROUTE_EXPIRY), [$param => $id]);
        Session::put('success', __('messages.paymentSuccessful'));
        
        return Reply::redirect($signedUrl, __('messages.paymentSuccessful'));
    }

}
