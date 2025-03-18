<?php

/**
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 24/05/17
 * Time: 11:29 PM
 */

namespace App\Traits;

use App\Models\SocialAuthSetting;
use Illuminate\Support\Facades\Config;

trait SocialAuthSettings
{

    public function setSocailAuthConfigs()
    {
        $settings = SocialAuthSetting::first();

        Config::set('services.facebook.client_id', ($settings->facebook_client_id) ?: env('FACEBOOK_CLIENT_ID'));
        Config::set('services.facebook.client_secret', ($settings->facebook_secret_id) ?: env('FACEBOOK_CLIENT_SECRET'));
        Config::set('services.facebook.redirect', $this->updateMainAppUrl(route('social_login_callback', 'facebook')));

        Config::set('services.google.client_id', ($settings->google_client_id) ?: env('GOOGLE_CLIENT_ID'));
        Config::set('services.google.client_secret', ($settings->google_secret_id) ?: env('GOOGLE_CLIENT_SECRET'));
        Config::set('services.google.redirect', $this->updateMainAppUrl(route('social_login_callback', 'google')));

        Config::set('services.twitter-oauth-2.client_id', ($settings->twitter_client_id) ?: env('TWITTER_CLIENT_ID'));
        Config::set('services.twitter-oauth-2.client_secret', ($settings->twitter_secret_id) ?: env('TWITTER_CLIENT_SECRET'));
        Config::set('services.twitter-oauth-2.redirect', $this->updateMainAppUrl(route('social_login_callback', 'twitter')));

        Config::set('services.linkedin-openid.client_id', ($settings->linkedin_client_id) ?: env('LINKEDIN_CLIENT_ID'));
        Config::set('services.linkedin-openid.client_secret', ($settings->linkedin_secret_id) ?: env('LINKEDIN_CLIENT_SECRET'));
        Config::set('services.linkedin-openid.redirect', $this->updateMainAppUrl(route('social_login_callback', 'linkedin')));
    }

    private function updateMainAppUrl($url)
    {
        if (isWorksuiteSaas() && module_enabled('Subdomain')) {
            $appUrl = config('app.main_app_url');
            $appUrl = str($appUrl)->after('://')->before('/');
            $currentUrl = str(url('/'))->after('://')->before('/');
            $url = str($url)->replace($currentUrl, $appUrl)->__toString();
        }

        return $url;
    }

}
