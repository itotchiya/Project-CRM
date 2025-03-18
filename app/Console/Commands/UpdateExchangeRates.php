<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\Invoice;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Expense;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-exchange-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the exchange rates for all the currencies in currencies table.';


    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $globalSetting = GlobalSetting::first();

        if (!$globalSetting) {
            return Command::SUCCESS;
        }

        $currencyApiKey = ($globalSetting->currency_converter_key) ?: config('app.currency_converter_key');

        if($globalSetting->currency_key_version == 'dedicated'){
            $currencyApiKeyVersion = $globalSetting->dedicated_subdomain;
        }else{
            $currencyApiKeyVersion = $globalSetting->currency_key_version;
        }

        if ($currencyApiKey && $currencyApiKeyVersion) {

            $client = new Client();

            Company::with(['currencies', 'currency'])
                ->chunk(50, function ($companies) use ($currencyApiKey, $currencyApiKeyVersion, $client) {
                    foreach ($companies as $company) {
                        $company->currencies->each(function ($currency) use ($currencyApiKey, $currencyApiKeyVersion, $company, $client) {
                            try {
                                $response = $client->request('GET', 'https://' . $currencyApiKeyVersion . '.currconv.com/api/v7/convert?q=' . $currency->currency_code . '_' . $company->currency->currency_code . '&compact=ultra&apiKey=' . $currencyApiKey);
                                $response = json_decode($response->getBody(), true);
                                $currency->exchange_rate = $response[$currency->currency_code . '_' . $company->currency->currency_code];
                                $currency->saveQuietly();
                            } catch (Exception $e) {
//                                    echo $e->getMessage();
                            }

                        });
                    }
                });


            $this->invoices();
            $this->payments();
            $this->expenses();

            return Command::SUCCESS;

        }
    }

    private function invoices()
    {
        $invoices = Invoice::all();

        foreach ($invoices as $invoice) {
            $currency = Currency::where('id', $invoice->currency_id)->first();

            if ($currency) {
                $invoice->exchange_rate = $currency->exchange_rate;
                $invoice->save();
            }

        }
    }

    private function payments()
    {
        $payments = Payment::all();

        foreach ($payments as $payment) {
            $currency = Currency::where('id', $payment->currency_id)->first();

            if ($currency) {
                $payment->exchange_rate = $currency->exchange_rate;
                $payment->saveQuietly();
            }
        }
    }

    private function expenses()
    {
        $expenses = Expense::all();

        foreach ($expenses as $expense) {
            $currency = Currency::where('id', $expense->currency_id)->first();

            if ($currency) {
                $expense->exchange_rate = $currency->exchange_rate;
                $expense->save();
            }
        }
    }

}
