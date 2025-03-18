<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\SignUpSetting\SignUpSettingRequest;
use App\Models\GlobalSetting;

class SignUpSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.signUpSetting');
        $this->activeSettingMenu = 'sign_up_setting';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_company_setting') !== 'all');
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->globalSetting = GlobalSetting::first();
        return view('sign-up-settings.index', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SignUpSettingRequest $request)
    {
        $sign_up_settings = GlobalSetting::first();
        $sign_up_settings->sign_up_terms = ($request->sign_up_terms == 'yes') ? 'yes' : 'no';
        $sign_up_settings->terms_link = ($request->sign_up_terms == 'yes') ? $request->terms_link : null;
        $sign_up_settings->save();

        cache()->forget('global_setting');

        return Reply::success(__('messages.updateSuccess'));
    }

}
