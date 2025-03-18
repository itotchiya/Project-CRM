<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\UpdateContractSetting;
use App\Models\InvoiceSetting;

class ContractSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.contractSettings';
        $this->activeSettingMenu = 'contract_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_contract_setting') == 'all' && in_array('contracts', user_modules())));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $this->contractSetting = InvoiceSetting::first();

        return view('contract-settings.index', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractSetting $request, string $id)
    {
        $setting = InvoiceSetting::findOrFail($id);
        $setting->contract_prefix = $request->contract_prefix;
        $setting->contract_number_separator = $request->contract_number_separator;
        $setting->contract_digit = $request->contract_digit;
        $setting->save();

        session()->forget('invoice_setting');
        session()->forget('company');

        return Reply::success(__('messages.updateSuccess'));
    }

}
