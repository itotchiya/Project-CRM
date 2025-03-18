<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\GoogleCaptcha\UpdateGoogleCaptchaSetting;
use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class SecuritySettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.securitySettings');
        $this->activeSettingMenu = 'security_settings';
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $this->user = user();
        $this->smtpSetting = smtp_setting();
        $this->setting = company();

        $this->view = 'security-settings.ajax.google-recaptcha';

        $tab = request('tab');

        switch ($tab) {
        case 'recaptcha':
            abort_403(user()->permission('manage_security_setting') !== 'all');
            $this->view = 'security-settings.ajax.google-recaptcha';
                break;
        default:
            $this->view = 'security-settings.ajax.two-factor-authentication';
                break;
        }

        $this->activeTab = $tab ?: '2fa';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => $this->pageTitle
            ]);
        }

        return view('security-settings.index', $this->data);
    }

    /**
     * @param UpdateGoogleCaptchaSetting $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    //phpcs:ignore
    public function update(UpdateGoogleCaptchaSetting $request, $id)
    {
        $google_captcha_setting = GlobalSetting::first();

        if ($request->version == 'v3') {
            $google_captcha_setting->google_recaptcha_v3_site_key = $request->google_recaptcha_v3_site_key;
            $google_captcha_setting->google_recaptcha_v3_secret_key = $request->google_recaptcha_v3_secret_key;
            $google_captcha_setting->google_recaptcha_v3_status = 'active';
            $google_captcha_setting->google_recaptcha_v2_status = 'deactive';
        }
        elseif ($request->version == 'v2') {
            $google_captcha_setting->google_recaptcha_v2_site_key = $request->google_recaptcha_v2_site_key;
            $google_captcha_setting->google_recaptcha_v2_secret_key = $request->google_recaptcha_v2_secret_key;
            $google_captcha_setting->google_recaptcha_v2_status = 'active';
            $google_captcha_setting->google_recaptcha_v3_status = 'deactive';
        }

        if (!$request->google_recaptcha_status) {
            $google_captcha_setting->google_recaptcha_v2_status = 'deactive';
            $google_captcha_setting->google_recaptcha_v3_status = 'deactive';
            $google_captcha_setting->google_recaptcha_status = 'deactive';
        }
        elseif ($request->google_recaptcha_status) {
            $google_captcha_setting->google_recaptcha_status = 'active';
        }

        $google_captcha_setting->save();

        cache()->forget('global_setting');

        if ($request->cache) {
            Artisan::call('optimize');
            Artisan::call('route:clear');
        }
        else {
            Artisan::call('optimize:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function verify()
    {
        $this->key = request()->key;
        return view('security-settings.verify-recaptcha-v3', $this->data);
    }

}
