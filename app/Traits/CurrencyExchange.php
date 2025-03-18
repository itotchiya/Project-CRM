<?php

/**
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 23/11/17
 * Time: 6:07 PM
 */

namespace App\Traits;

use App\Models\Currency;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Throwable;

trait CurrencyExchange
{

    public function updateExchangeRates()
    {
        $setting = company();

        if (!$setting) {
            return true;
        }

        $currencies = Currency::where('id', '<>', $setting->currency_id)->get();

        if($setting->currency_key_version == 'dedicated'){
            $currencyApiKeyVersion = $setting->dedicated_subdomain;
        }else{
            $currencyApiKeyVersion = $setting->currency_key_version;
        }

        $currencyApiKey = $setting->currency_converter_key ?: env('CURRENCY_CONVERTER_KEY');

        $baseCurrency = $setting->currency;
        $baseCurrency->exchange_rate = 1;
        $baseCurrency->saveQuietly();

        if ($currencyApiKey === null) {
            return false;
        }

        $client = new Client();

        foreach ($currencies as $currency) {
            try {
                $currency = Currency::findOrFail($currency->id);

                $apiUrl = 'https://' . $currencyApiKeyVersion . '.currconv.com/api/v7/convert?q=';

                if ($currency->is_cryptocurrency == 'no') {
                    // Get exchange rate for non-cryptocurrency
                    $res = $client->request('GET', $apiUrl . $currency->currency_code . '_' . $baseCurrency->currency_code . '&compact=ultra&apiKey=' . $currencyApiKey);
                }
                else {
                    // Get exchange rate for cryptocurrency
                    $res = $client->request('GET', $apiUrl . $currency->currency_code . '_USD&compact=ultra&apiKey=' . $currencyApiKey);
                }

                $conversionRate = json_decode($res->getBody(), true);

                if (!empty($conversionRate)) {
                    $currency->exchange_rate = $conversionRate[mb_strtoupper($currency->currency_code) . '_' . $baseCurrency->currency_code];
                    $currency->save();
                }
            } catch (Throwable $th) {
                Log::info($th);
            }
        }
    }

}
