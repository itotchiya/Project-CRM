<?php

namespace App\Traits;

use Pusher\Pusher;

trait pusherConfigTrait
{

    /**
     * @throws PusherException
     */
    public function triggerPusher($channel, $event, $data): void
    {
        $pusherSetting = pusher_settings();

        if ($pusherSetting->status) {

            $pusher = new Pusher(
                $pusherSetting->pusher_app_key,
                $pusherSetting->pusher_app_secret,
                $pusherSetting->pusher_app_id,
                [
                    'cluster' => $pusherSetting->pusher_cluster,
                    'useTLS' => $pusherSetting->force_tls
                ]
            );

            $pusher->trigger($channel, $event, $data);
        }

    }

}
