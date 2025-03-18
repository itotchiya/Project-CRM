<?php

namespace App\Providers;

use Illuminate\Session\SessionServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class SessionDriverConfigProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        try {

            // TODO: To be removed in next update
            $setting = DB::table('global_settings')->first();

            if ($setting) {
                Config::set('session.driver', $setting->session_driver != '' ? $setting->session_driver : 'file');
                Config::set('app.cron_timezone', $setting->timezone);
            }

        }
        // @codingStandardsIgnoreLine
        catch (\Exception $e) {
        }

        $app = App::getInstance();
        $app->register(SessionServiceProvider::class);
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
