<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\PusherSetting\UpdateRequest;
use App\Models\PusherSetting;
use App\Traits\pusherConfigTrait;
use Pusher\Pusher;

class PusherSettingsController extends AccountBaseController
{

    use pusherConfigTrait;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.pusherSettings';
        $this->pageIcon = 'icon-settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_notification_setting') !== 'all');

            return $next($request);
        });
    }

    // phpcs:ignore
    public function update(UpdateRequest $request, $id)
    {
        if ($request->status == 'active') {
            $checkPusher = new Pusher(
                $request->pusher_app_key,
                $request->pusher_app_secret,
                $request->pusher_app_id,
                [
                    'cluster' => $request->pusher_cluster,
                    'useTLS' => $request->force_tls
                ]
            );
    
            try {
                $checkPusher->trigger('test-pusher-channel', 'test-pusher-message', ['message' => 'done']);
            } catch(\Exception $e) {
                return Reply::dataOnly(['error' => $e->getMessage()]);
            }
        }

        $pusher = pusher_settings();
        $pusher->pusher_app_id = $request->pusher_app_id;
        $pusher->pusher_app_key = $request->pusher_app_key;
        $pusher->pusher_app_secret = $request->pusher_app_secret;
        $pusher->pusher_cluster = $request->pusher_cluster;
        $pusher->force_tls = $request->force_tls;
        $pusher->status = $request->status == 'active' ? 1 : 0;
        $pusher->taskboard = $request->taskboard ? 1 : 0;
        $pusher->messages = $request->messages ? 1 : 0;
        $pusher->save();

        session(['pusher_settings' => PusherSetting::first()]);

        return Reply::successWithData(__('messages.updateSuccess'), ['status' => $pusher->status]);
    }

}
