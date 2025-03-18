<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{

    public function register()
    {
        try {
            $pusherSetting = DB::table('pusher_settings')->first();

            if ($pusherSetting) {

                if (!in_array(config('app.env'), ['demo', 'development'])) {

                    $driver = ($pusherSetting->status == 1) ? 'pusher' : 'null';

                    Config::set('broadcasting.default', $driver);
                    Config::set('broadcasting.connections.pusher.key', $pusherSetting->pusher_app_key);
                    Config::set('broadcasting.connections.pusher.secret', $pusherSetting->pusher_app_secret);
                    Config::set('broadcasting.connections.pusher.app_id', $pusherSetting->pusher_app_id);
                    Config::set('broadcasting.connections.pusher.options.host', 'api-'.$pusherSetting->pusher_cluster.'.pusher.com');
                }
            }
        }
        // @codingStandardsIgnoreLine
        catch (\Exception $e) {
        } // phpcs:ignore
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }

}
