<?php

namespace App\Http\Controllers;

use App\Http\Requests\Currency\UpdateCurrency;
use App\Models\Currency;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Http\Requests\Currency\StoreCurrency;
use App\Http\Requests\Currency\StoreCurrencyExchangeKey;
use App\Models\Company;
use App\Models\CurrencyFormatSetting;
use GuzzleHttp\Client;
use App\Traits\CurrencyExchange;

class CurrencySettingController extends AccountBaseController
{

    use CurrencyExchange;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.currencySettings';
        $this->activeSettingMenu = 'currency_settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_currency_setting') !== 'all');

            return $next($request);
        });
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|mixed
     */
    public function index()
    {
        $this->currencies = Currency::all();

        $this->defaultFormattedCurrency = currency_format('1234567.89', companyOrGlobalSetting()->currency_id);

        $this->view = 'currency-settings.ajax.currency-setting';

        $this->activeTab = 'currency-setting';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('currency-settings.index', $this->data);

    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->currencies = Currency::all();
        $this->currencyFormatSetting = currency_format_setting();

        $this->defaultFormattedCurrency = currency_format('1234567.89', companyOrGlobalSetting()->currency_id);

        return view('currency-settings.create', $this->data);
    }

    /**
     * @param StoreCurrency $request
     * @return array|string[]
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreCurrency $request)
    {
        $currency = new Currency();
        $currency->currency_name = $request->currency_name;
        $currency->currency_symbol = $request->currency_symbol;
        $currency->currency_code = $request->currency_code;
        $currency->is_cryptocurrency = $request->is_cryptocurrency;
        $currency->exchange_rate = $request->exchange_rate;
        $currency->usd_price = $request->usd_price;
        $currency->currency_position = $request->currency_position;
        $currency->no_of_decimal = $request->no_of_decimal;
        $currency->thousand_separator = $request->thousand_separator;
        $currency->decimal_separator = $request->decimal_separator;
        $currency->save();

        $this->updateExchangeRates();

        return Reply::success(__('messages.recordSaved'));
    }

    public function show($id)
    {
        return redirect(route('currency-settings.edit', $id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->currency = Currency::findOrFail($id);
        $this->defaultFormattedCurrency = currency_format('1234567.89', $id);

        return view('currency-settings.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCurrency $request, $id)
    {
        $currency = Currency::findOrFail($id);
        $currency->currency_name = $request->currency_name;
        $currency->currency_symbol = $request->currency_symbol;
        $currency->currency_code = $request->currency_code;
        $currency->exchange_rate = $request->exchange_rate;
        $currency->usd_price = $request->usd_price;
        $currency->is_cryptocurrency = $request->is_cryptocurrency;
        $currency->currency_position = $request->currency_position;
        $currency->no_of_decimal = $request->no_of_decimal;
        $currency->thousand_separator = $request->thousand_separator;
        $currency->decimal_separator = $request->decimal_separator;
        $currency->save();

        session()->forget('currency_format_setting' . $currency->id);
        session()->forget('currency_format_setting');

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        if ($this->company->currency_id == $id) {
            return Reply::error(__('modules.currencySettings.cantDeleteDefault'));
        }

        try {
            Currency::destroy($id);
        } catch (QueryException) {
            return Reply::error(__('messages.notAllowedToDeleteCurrency'));
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function exchangeRate($currency)
    {
        $currencyApiKey = ($this->global->currency_converter_key) ?: config('app.currency_converter_key');

        if($this->global->currency_key_version == 'dedicated'){
            $currencyApiKeyVersion = $this->global->dedicated_subdomain;
        }else{
            $currencyApiKeyVersion = $this->global->currency_key_version;
        }

        try {
            // Get exchange rate
            $client = new Client();
            $res = $client->request('GET', 'https://' . $currencyApiKeyVersion . '.currconv.com/api/v7/convert?q=' . $currency . '_' . companyOrGlobalSetting()->currency->currency_code . '&compact=ultra&apiKey=' . $currencyApiKey);
            $conversionRate = $res->getBody();
            $conversionRate = json_decode($conversionRate, true);
            $rate = $conversionRate[mb_strtoupper($currency) . '_' . companyOrGlobalSetting()->currency->currency_code];

            return Reply::dataOnly(['status' => 'success', 'value' => $rate]);

        } catch (\Throwable $th) {
            return Reply::error($th->getMessage());
        }
    }

    /**
     * @return array
     */
    public function updateExchangeRate()
    {
        $currencyApiKey = ($this->global->currency_converter_key) ?: config('app.currency_converter_key');

        if (is_null($currencyApiKey)) {
            return Reply::error(__('messages.currencyExchangeKeyNotFound'));
        }

        $this->updateExchangeRates();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function currencyExchangeKey()
    {
        return view('currency-settings.currency-exchange-modal', $this->data);
    }

    /**
     * @param StoreCurrencyExchangeKey $request
     * @return array
     */
    public function currencyExchangeKeyStore(StoreCurrencyExchangeKey $request)
    {
        $this->global->currency_converter_key = $request->currency_converter_key;
        $this->global->currency_key_version = $request->currency_key_version;

        if($request->currency_key_version == 'dedicated'){
            $this->global->dedicated_subdomain = $request->dedicated_subdomain;
        }else{
            $this->global->dedicated_subdomain = null;
        }
        $this->global->save();

        // remove session
        cache()->forget('global_setting');


        return Reply::success(__('messages.updateSuccess'));
    }

}
