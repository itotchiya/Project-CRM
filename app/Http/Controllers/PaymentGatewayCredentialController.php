<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\PaymentGateway\UpdateGatewayCredentials;
use App\Models\Currency;
use App\Models\OfflinePaymentMethod;
use App\Models\PaymentGatewayCredentials;
use Illuminate\Contracts\Encryption\DecryptException;

class PaymentGatewayCredentialController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.paymentGatewayCredential';
        $this->activeSettingMenu = 'payment_gateway_settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_payment_setting') !== 'all');

            return $next($request);
        });
    }

    public function index()
    {
        $this->credentials = PaymentGatewayCredentials::first();
        $this->offlineMethods = OfflinePaymentMethod::all();
        $this->currencies = Currency::all();
        $this->updateRoute = route('payment-gateway-settings.update', [$this->credentials->id]);
        $hash = $this->company->hash;

        $tab = request('tab');

        // TODO: to be removed in future updates
        $this->fixPayloadMismatch($this->credentials);

        switch ($tab) {
        case 'stripe':

            $this->webhookRoute = route('stripe.webhook', [$hash]);
            $this->view = 'payment-gateway-settings.ajax.stripe';
            break;
        case 'razorpay':
            $this->webhookRoute = route('razorpay.webhook', [$hash]);
            $this->view = 'payment-gateway-settings.ajax.razorpay';
            break;
        case 'paystack':
            $this->webhookRoute = route('paystack.webhook', [$hash]);
            $this->view = 'payment-gateway-settings.ajax.paystack';
            break;
        case 'flutterwave':
            $this->webhookRoute = route('flutterwave.webhook', [$hash]);
            $this->view = 'payment-gateway-settings.ajax.flutterwave';
            break;
        case 'mollie':
            $this->view = 'payment-gateway-settings.ajax.mollie';
            break;
        case 'payfast':
            $this->view = 'payment-gateway-settings.ajax.payfast';
            break;
        case 'authorize':
            $this->view = 'payment-gateway-settings.ajax.authorize';
            break;
        case 'square':
            $this->webhookRoute = route('square.webhook', [$hash]);
            $this->view = 'payment-gateway-settings.ajax.square';
            break;
        case 'offline':
            $this->view = 'payment-gateway-settings.ajax.offline';
            break;
        default:
            $this->webhookRoute = route('paypal.webhook', [$hash]);
            $this->view = 'payment-gateway-settings.ajax.paypal';
            break;
        }

        $this->activeTab = $tab ?: 'paypal';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('payment-gateway-settings.index', $this->data);
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateGatewayCredentials $request, $id)
    {
        $credential = PaymentGatewayCredentials::findOrFail($id);

        $method = $request->payment_method;

        switch ($method) {
        case 'stripe':
            $this->stripe($request, $credential);
            break;
        case 'razorpay':
            $this->razorpay($request, $credential);
            break;
        case 'paystack':
            $this->paystack($request, $credential);
            break;
        case 'flutterwave':
            $this->flutterwave($request, $credential);
            break;
        case 'mollie':
            $this->mollie($request, $credential);
            break;
        case 'payfast':
            $this->payfast($request, $credential);
            break;
        case 'authorize':
            $this->authorizeSave($request, $credential);
            break;
        case 'square':
            $this->square($request, $credential);
            break;
        default:
            $this->paypal($request, $credential);
            break;
        }

        $credential->save();

        return Reply::success(__('messages.updateSuccess'));

    }

    private function paypal($request, $credential)
    {
        if ($request->payment_method == 'paypal') {
            $credential->paypal_mode = $request->paypal_mode;

            if ($request->paypal_mode == 'sandbox') {
                $credential->sandbox_paypal_client_id = $request->sandbox_paypal_client_id;
                $credential->sandbox_paypal_secret = $request->sandbox_paypal_secret;
            }
            else {
                $credential->paypal_client_id = $request->live_paypal_client_id;
                $credential->paypal_secret = $request->live_paypal_secret;
            }

            $credential->paypal_status = $request->paypal_status ? 'active' : 'deactive';
        }
    }

    private function stripe($request, $credential)
    {
        if ($request->stripe_mode == 'test') {
            $credential->test_stripe_client_id = $request->test_stripe_client_id;
            $credential->test_stripe_secret = $request->test_stripe_secret;
            $credential->test_stripe_webhook_secret = $request->test_stripe_webhook_secret;
        }
        else {
            $credential->live_stripe_client_id = $request->live_stripe_client_id;
            $credential->live_stripe_secret = $request->live_stripe_secret;
            $credential->live_stripe_webhook_secret = $request->live_stripe_webhook_secret;
        }

        $credential->stripe_mode = $request->stripe_mode;
        $credential->stripe_status = ($request->stripe_status) ? 'active' : 'deactive';
    }

    private function razorpay($request, $credential)
    {
        if ($request->razorpay_mode == 'test') {
            $credential->test_razorpay_key = $request->test_razorpay_key;
            $credential->test_razorpay_secret = $request->test_razorpay_secret;
            $credential->test_razorpay_webhook_secret = $request->test_razorpay_webhook_secret;
        }
        else {
            $credential->live_razorpay_key = $request->live_razorpay_key;
            $credential->live_razorpay_secret = $request->live_razorpay_secret;
            $credential->live_razorpay_webhook_secret = $request->live_razorpay_webhook_secret;
        }


        $credential->razorpay_mode = $request->razorpay_mode;
        $credential->razorpay_status = ($request->razorpay_status) ? 'active' : 'inactive';
    }

    private function paystack($request, $credential)
    {
        $credential->paystack_mode = $request->paystack_mode;
        $credential->paystack_key = $request->paystack_key;
        $credential->paystack_secret = $request->paystack_secret;
        $credential->paystack_merchant_email = $request->paystack_merchant_email;
        $credential->test_paystack_key = $request->test_paystack_key;
        $credential->test_paystack_secret = $request->test_paystack_secret;
        $credential->test_paystack_merchant_email = $request->test_paystack_merchant_email;

        $credential->paystack_status = ($request->paystack_status) ? 'active' : 'deactive';
    }

    private function mollie($request, $credential)
    {
        $credential->mollie_api_key = $request->mollie_api_key;

        $credential->mollie_status = ($request->mollie_status) ? 'active' : 'deactive';
    }

    private function payfast($request, $credential)
    {
        if ($request->payfast_mode == 'sandbox') {
            $credential->test_payfast_merchant_id = $request->test_payfast_merchant_id;
            $credential->test_payfast_merchant_key = $request->test_payfast_merchant_key;
            $credential->test_payfast_passphrase = $request->test_payfast_passphrase;
        }
        else {
            $credential->payfast_merchant_id = $request->payfast_merchant_id;
            $credential->payfast_merchant_key = $request->payfast_merchant_key;
            $credential->payfast_passphrase = $request->payfast_passphrase;
        }

        $credential->payfast_mode = $request->payfast_mode;
        $credential->payfast_status = ($request->payfast_status) ? 'active' : 'deactive';
    }

    private function authorizeSave($request, $credential)
    {
        $credential->authorize_api_login_id = $request->authorize_api_login_id;
        $credential->authorize_transaction_key = $request->authorize_transaction_key;
        $credential->authorize_environment = $request->authorize_environment;

        $credential->authorize_status = $request->authorize_status ? 'active' : 'deactive';
    }

    private function square($request, $credential)
    {
        $credential->square_application_id = $request->square_application_id;
        $credential->square_access_token = $request->square_access_token;
        $credential->square_location_id = $request->square_location_id;
        $credential->square_environment = $request->square_environment;

        $credential->square_status = $request->square_status ? 'active' : 'deactive';
    }

    private function flutterwave($request, $credential)
    {
        $credential->test_flutterwave_key = $request->test_flutterwave_key;
        $credential->test_flutterwave_secret = $request->test_flutterwave_secret;
        $credential->test_flutterwave_hash = $request->test_flutterwave_hash;
        $credential->live_flutterwave_key = $request->live_flutterwave_key;
        $credential->live_flutterwave_secret = $request->live_flutterwave_secret;
        $credential->live_flutterwave_hash = $request->live_flutterwave_hash;
        $credential->flutterwave_mode = $request->flutterwave_mode;
        $credential->flutterwave_webhook_secret_hash = $request->flutterwave_webhook_secret_hash;
        $credential->flutterwave_status = $request->flutterwave_status ? 'active' : 'deactive';
    }

    public function fixPayloadMismatch($gateway)
    {
        $casts = (new PaymentGatewayCredentials())->getCasts();

        // Filter out the encrypted fields
        $encryptedFields = array_keys(array_filter($casts, function ($value) {
            return $value === 'encrypted';
        }));

        try {
            // Attempt to access each encrypted field to check for decryption issues
            foreach ($encryptedFields as $field) {
                $gateway->$field;
            }
        } catch (DecryptException $e) {
            // If a decryption error occurs, set each encrypted field to null
            // when we get message like below set as null or o
            // The MAC is invalid.
            // The payload is invalid.
            foreach ($encryptedFields as $field) {
                $gateway->$field = null;
            }
            // Save the credentials after setting fields to null
            $gateway->save();
        }
    }

}
