<?php

namespace App\Providers;

use App\Traits\HasMaskImage;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class SmtpConfigProvider extends ServiceProvider
{
    use HasMaskImage;

    public function register()
    {
        try {
            // TODO: To be removed in next update
            $smtpSetting = DB::table('smtp_settings')->first();
            $settings = DB::table('global_settings')->first();

            if ($smtpSetting && $settings) {

                if (!in_array(config('app.env'), ['demo', 'development'])) {

                    $driver = ($smtpSetting->mail_driver != 'mail') ? $smtpSetting->mail_driver : 'sendmail';

                    $password = Crypt::decryptString($smtpSetting->mail_password);

                    Config::set('mail.default', $driver);
                    Config::set('mail.mailers.smtp.host', $smtpSetting->mail_host);
                    Config::set('mail.mailers.smtp.port', $smtpSetting->mail_port);
                    Config::set('mail.mailers.smtp.username', $smtpSetting->mail_username);
                    Config::set('mail.mailers.smtp.password', $password);
                    Config::set('mail.mailers.smtp.encryption', $smtpSetting->mail_encryption);

                    Config::set('mail.verified', $smtpSetting->email_verified ? true : false);
                    Config::set('queue.default', $smtpSetting->mail_connection);
                }

                Config::set('mail.from.name', $smtpSetting->mail_from_name);
                Config::set('mail.from.address', $smtpSetting->mail_from_email);

                Config::set('app.name', $settings->global_app_name);
                Config::set('app.global_app_name', $settings->global_app_name);

                if (is_null($settings->light_logo)) {
                    Config::set('app.logo', asset('img/worksuite-logo.png'));
                }
                else {
                    Config::set('app.logo', $this->generateMaskedImageAppUrl('app-logo/' . $settings->light_logo));
                }

                $pushSetting = DB::table('push_notification_settings')->first();

                if ($pushSetting) {
                    Config::set('services.onesignal.app_id', $pushSetting->onesignal_app_id);
                    Config::set('services.onesignal.rest_api_key', $pushSetting->onesignal_rest_api_key);
                    Config::set('onesignal.app_id', $pushSetting->onesignal_app_id);
                    Config::set('onesignal.rest_api_key', $pushSetting->onesignal_rest_api_key);
                }
            }
        }
        // @codingStandardsIgnoreLine
        catch (\Exception $e) {
        }

        $app = App::getInstance();
        $app->register(MailServiceProvider::class);

        $app = App::getInstance();
        $app->register( QueueServiceProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

}
