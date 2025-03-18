<?php

namespace App\Traits;

use App\Models\Company;
use Froiden\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\Config;

trait PaymentGatewayTrait
{

    private function paystackSet($companyHash)
    {

        // This needs to be set according to company id
        $paymentGateway = $this->getGateway($companyHash);

        $payStackMode = $paymentGateway->paystack_mode;

        if ($payStackMode == 'sandbox') {
            $key = ($paymentGateway->test_paystack_key) ?: env('PAYSTACK_PUBLIC_KEY');
            $apiSecret = ($paymentGateway->test_paystack_secret) ?: env('PAYSTACK_SECRET_KEY');
            $email = ($paymentGateway->test_paystack_merchant_email) ?: env('MERCHANT_EMAIL');
        }
        else {
            $key = ($paymentGateway->paystack_key) ?: env('PAYSTACK_PUBLIC_KEY');
            $apiSecret = ($paymentGateway->paystack_secret) ?: env('PAYSTACK_SECRET_KEY');
            $email = ($paymentGateway->paystack_merchant_email) ?: env('MERCHANT_EMAIL');
        }

        $url = ($paymentGateway->paystack_payment_url) ?: env('PAYSTACK_PAYMENT_URL');

        Config::set('paystack.publicKey', $key);
        Config::set('paystack.secretKey', $apiSecret);
        Config::set('paystack.paymentUrl', $url);
        Config::set('paystack.merchantEmail', $email);

    }

    private function mollieSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);
        $mollie_api_key = ($paymentGateway->mollie_api_key) ?: config('mollie.key');
        Config::set('mollie.key', $mollie_api_key);
    }

    private function payfastSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);

        if ($paymentGateway->payfast_mode == 'sandbox') {
            $payfast_merchant_id = ($paymentGateway->test_payfast_merchant_id) ?: env('PF_MERCHANT_ID');
            $payfast_merchant_key = ($paymentGateway->test_payfast_merchant_key) ?: env('PF_MERCHANT_KEY');
            $payfast_passphrase = ($paymentGateway->test_payfast_passphrase) ?: env('PAYFAST_PASSPHRASE');
        }
        else {
            $payfast_merchant_id = ($paymentGateway->payfast_merchant_id) ?: env('PF_MERCHANT_ID');
            $payfast_merchant_key = ($paymentGateway->payfast_merchant_key) ?: env('PF_MERCHANT_KEY');
            $payfast_passphrase = ($paymentGateway->payfast_passphrase) ?: env('PAYFAST_PASSPHRASE');
        }

        $payfast_mode = ($paymentGateway->payfast_mode == 'sandbox');

        Config::set('payfast.merchant.merchant_id', $payfast_merchant_id);
        Config::set('payfast.merchant.merchant_key', $payfast_merchant_key);
        Config::set('payfast.passphrase', $payfast_passphrase);
        Config::set('payfast.testing', $payfast_mode);

    }

    private function flutterwaveSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);
        // Flutterwave
        $flutterwave_mode = $paymentGateway->flutterwave_mode;

        if ($flutterwave_mode == 'sandbox') {
            $flutterwave_key = ($paymentGateway->test_flutterwave_key) ?: env('FLW_PUBLIC_KEY');
            $flutterwave_secret = ($paymentGateway->test_flutterwave_secret) ?: env('FLW_SECRET_KEY');
            $flutterwave_hash = ($paymentGateway->test_flutterwave_hash) ?: env('FLW_SECRET_HASH');
        }
        else {
            $flutterwave_key = ($paymentGateway->live_flutterwave_key) ?: env('FLW_PUBLIC_KEY');
            $flutterwave_secret = ($paymentGateway->live_flutterwave_secret) ?: env('FLW_SECRET_KEY');
            $flutterwave_hash = ($paymentGateway->live_flutterwave_hash) ?: env('FLW_SECRET_HASH');
        }


        Config::set('flutterwave.publicKey', $flutterwave_key);
        Config::set('flutterwave.secretKey', $flutterwave_secret);
        Config::set('secretHash.merchantEmail', $flutterwave_hash);
    }

    private function authorizeSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);
        $authorize_api_login_id = ($paymentGateway->authorize_api_login_id) ?: env('AUTHORIZE_PAYMENT_API_LOGIN_ID');
        $authorize_transaction_key = ($paymentGateway->authorize_transaction_key) ?: env('AUTHORIZE_PAYMENT_TRANSACTION_KEY');

        $authorize_environment = ($paymentGateway->authorize_environment == 'sandbox');

        Config::set('services.authorize.login', $authorize_api_login_id);
        Config::set('services.authorize.transaction', $authorize_transaction_key);
        Config::set('services.authorize.sandbox', $authorize_environment);


    }

    private function squareSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);
        // square
        $square_application_id = ($paymentGateway->square_application_id) ?: env('SQUARE_APPLICATION_ID');
        $square_access_token = ($paymentGateway->square_access_token) ?: env('SQUARE_ACCESS_TOKEN');
        $square_location_id = ($paymentGateway->square_location_id) ?: env('SQUARE_LOCATION_ID');

        $square_environment = $paymentGateway->square_environment;

        Config::set('services.square.application_id', $square_application_id);
        Config::set('services.square.access_token', $square_access_token);
        Config::set('services.square.location_id', $square_location_id);
        Config::set('services.square.environment', $square_environment);
    }

    private function getGateway($companyHash)
    {

        $company = Company::where('hash', $companyHash)->first();

        if (!$company) {
            throw new ApiException('Please enter the correct webhook url. You have entered wrong webhook url', null, 200);
        }

        // This needs to be set according to company id
        return $company->paymentGatewayCredentials;
    }

}
