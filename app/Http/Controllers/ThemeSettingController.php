<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\UpdateThemeSetting;
use App\Models\GlobalSetting;
use App\Models\ThemeSetting;

class ThemeSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.themeSettings';
        $this->activeSettingMenu = 'theme_settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_theme_setting') !== 'all');

            return $next($request);
        });
    }

    public function index()
    {
        $themeSetting = ThemeSetting::get();

        // Get theme from single database query and then grouby panel as key
        $themes = $themeSetting->groupBy('panel');

        $this->adminTheme = $themes['admin'][0];
        $this->projectAdminTheme = $themes['project_admin'][0];
        $this->employeeTheme = $themes['employee'][0];
        $this->clientTheme = $themes['client'][0];

        return view('theme-settings.index', $this->data);
    }

    /**
     * @param UpdateThemeSetting $request
     * @return array
     */
    public function store(UpdateThemeSetting $request)
    {
        $setting = $this->company;

        $adminTheme = ThemeSetting::where('panel', 'admin')->first();
        $this->themeUpdate($adminTheme, $request->theme_settings[1], $request->primary_color[0]);

        $employeeTheme = ThemeSetting::where('panel', 'employee')->first();
        $this->themeUpdate($employeeTheme, $request->theme_settings[3], $request->primary_color[1]);

        $clientTheme = ThemeSetting::where('panel', 'client')->first();
        $this->themeUpdate($clientTheme, $request->theme_settings[4], $request->primary_color[2]);

        $setting->logo_background_color = $request->logo_background_color;
        $setting->auth_theme = $request->auth_theme;
        $setting->auth_theme_text = $request->auth_theme_text;
        $setting->app_name = $request->app_name;
        $setting->header_color = $request->global_header_color;

        if ($request->logo_delete == 'yes') {
            Files::deleteFile($setting->logo, GlobalSetting::APP_LOGO_PATH);
            $setting->logo = null;
        }

        if ($request->hasFile('logo')) {
            Files::deleteFile($setting->logo, GlobalSetting::APP_LOGO_PATH);
            $setting->logo = Files::uploadLocalOrS3($request->logo, GlobalSetting::APP_LOGO_PATH, width: 400);
        }


        if ($request->light_logo_delete == 'yes') {
            Files::deleteFile($setting->light_logo, GlobalSetting::APP_LOGO_PATH);
            $setting->light_logo = null;
        }

        if ($request->hasFile('light_logo')) {

            Files::deleteFile($setting->light_logo, GlobalSetting::APP_LOGO_PATH);
            $setting->light_logo = Files::uploadLocalOrS3($request->light_logo, GlobalSetting::APP_LOGO_PATH, width: 400);
        }

        if ($request->login_background_delete == 'yes') {
            Files::deleteFile($setting->login_background, 'login-background');
            $setting->login_background = null;
        }

        if ($request->hasFile('login_background')) {
            Files::deleteFile($setting->login_background, 'login-background');
            $setting->login_background = Files::uploadLocalOrS3($request->login_background, 'login-background');
        }


        if ($request->favicon_delete == 'yes') {
            Files::deleteFile($setting->favicon, 'favicon');
            $setting->favicon = null;
        }

        if ($request->hasFile('favicon')) {
            $setting->favicon = Files::uploadLocalOrS3($request->favicon, 'favicon', width: 50);
        }

        $setting->sidebar_logo_style = $request->sidebar_logo_style;

        $setting->save();
        session()->forget(['admin_theme', 'employee_theme', 'client_theme', 'company', 'companyOrGlobalSetting', 'user.company']);
        cache()->forget('global_setting');

        return Reply::redirect(route('theme-settings.index'), __('messages.updateSuccess'));
    }

    private function themeUpdate($updateObject, $themeSetting, $primaryColor)
    {
        $updateObject->header_color = $primaryColor;
        $updateObject->sidebar_theme = $themeSetting['sidebar_theme'];
        $updateObject->save();
        session()->forget(['admin_theme', 'employee_theme', 'client_theme']);
    }
}
