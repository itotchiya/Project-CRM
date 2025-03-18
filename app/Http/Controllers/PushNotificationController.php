<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\PushSetting\UpdateRequest;
use App\Models\EmailNotificationSetting;
use App\Models\PushNotificationSetting;
use App\Models\User;
use App\Notifications\TestPush;

class PushNotificationController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.pushNotifications';
        $this->activeSettingMenu = 'notification_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_notification_setting') == 'all'));
            return $next($request);
        });
    }

    //phpcs:ignore
    public function update(UpdateRequest $request, $id)
    {
        $this->savePushNotificationSettings($request);

        $setting = PushNotificationSetting::first();
        $setting->onesignal_app_id = $request->onesignal_app_id;
        $setting->onesignal_rest_api_key = $request->onesignal_rest_api_key;
        $setting->status = ($request->has('status') ? $request->status : 'inactive');
        $setting->beams_push_status = ($request->has('beams_push_status') ? $request->beams_push_status : 'inactive');
        $setting->instance_id = $request->instance_id;
        $setting->beam_secret = $request->beam_secret;
        $setting->save();

        session()->forget('email_notification_setting');
        cache()->forget('push_setting');

        return Reply::success(__('messages.updateSuccess'));
    }

    public function sendTestNotification()
    {
        $user = User::findOrFail($this->user->id);

        $setting = PushNotificationSetting::first();
        if ($setting->beams_push_status == 'active') {
            // Notify User
            $user->notify(new TestPush());
        } else {
            // Notify User
            $user->notify(new TestPush());
        }


        return Reply::success('Test notification sent.');
    }

    public function savePushNotificationSettings($request)
    {
        EmailNotificationSetting::where('send_push', 'yes')->update(['send_push' => 'no']);

        if($request->send_push) {
            EmailNotificationSetting::whereIn('id', $request->send_push)->update(['send_push' => 'yes']);
        }
    }

}
