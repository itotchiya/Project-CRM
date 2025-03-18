<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\UpdateQuickBooksSetting;
use App\Models\QuickBooksSetting;

class QuickbookSettingsController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.quickbookSettings';
        $this->pageIcon = 'icon-settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_finance_setting') !== 'all');
            return $next($request);
        });
    }

    public function update(UpdateQuickBooksSetting $request)
    {
        $credential = QuickBooksSetting::first();

        if ($request->environment == 'Development') {
            $credential->sandbox_client_id = $request->sandbox_client_id;
            $credential->sandbox_client_secret = $request->sandbox_client_secret;

            if ($credential->isDirty('sandbox_client_id') || $credential->isDirty('sandbox_client_secret')) {
                $credential->access_token = null;
            }
        }
        else {
            $credential->client_id = $request->client_id;
            $credential->client_secret = $request->client_secret;

            if ($credential->isDirty('client_id') || $credential->isDirty('client_secret')) {
                $credential->access_token = null;
            }
        }

        $credential->environment = $request->environment;
        $credential->status = $request->status ? 1 : 0;

        $credential->save();

        return Reply::redirect(route('invoice-settings.index').'?tab=quickbooks', __('messages.updateSuccess'));
    }

}
