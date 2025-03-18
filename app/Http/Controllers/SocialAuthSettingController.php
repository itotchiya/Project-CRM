<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\SocialAuthSetting;
use App\Http\Requests\Admin\SocialAuth\UpdateRequest;

class SocialAuthSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.socialLogin';
        $this->activeSettingMenu = 'social_auth_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_social_login_setting') == 'all'));
            return $next($request);
        });
    }

    public function index()
    {
        $this->credentials = SocialAuthSetting::first();

        $tab = request('tab');

        $this->view = match ($tab) {
            'facebook' => 'social-login-settings.ajax.facebook',
            'twitter' => 'social-login-settings.ajax.twitter',
            'linkedin' => 'social-login-settings.ajax.linkedin',
            default => 'social-login-settings.ajax.google',
        };

        $this->activeTab = $tab ?: 'google';


        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('social-login-settings.index', $this->data);
    }

    public function update(UpdateRequest $request)
    {
        $socialAuth = SocialAuthSetting::first();

        if($request->tab == 'twitter') {
            $socialAuth->twitter_client_id = $request->twitter_client_id;
            $socialAuth->twitter_secret_id = $request->twitter_secret_id;
            $socialAuth->twitter_status = $request->twitter_status ? 'enable' : 'disable';
        }

        if($request->tab == 'facebook') {
            $socialAuth->facebook_client_id = $request->facebook_client_id;
            $socialAuth->facebook_secret_id = $request->facebook_secret_id;
            $socialAuth->facebook_status = $request->facebook_status ? 'enable' : 'disable';
        }

        if($request->tab == 'linkedin') {
            $socialAuth->linkedin_client_id = $request->linkedin_client_id;
            $socialAuth->linkedin_secret_id = $request->linkedin_secret_id;
            $socialAuth->linkedin_status = $request->linkedin_status ? 'enable' : 'disable';
        }

        if($request->tab == 'google') {
            $socialAuth->google_client_id = $request->google_client_id;
            $socialAuth->google_secret_id  = $request->google_secret_id;
            $socialAuth->google_status = $request->google_status ? 'enable' : 'disable';
        }

        $socialAuth->save();

        cache()->forget('social_auth_setting');

        return Reply::success(__('messages.updateSuccess'));
    }

}
