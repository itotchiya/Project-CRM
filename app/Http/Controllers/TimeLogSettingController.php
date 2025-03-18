<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\TimeLogSetting\UpdateTimeLog;
use App\Models\LogTimeFor;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

class TimeLogSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.timeLogSettings';
        $this->activeSettingMenu = 'timelog_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_time_log_setting') == 'all' && in_array('timelogs', user_modules())));

            return $next($request);
        });
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->data['logTime'] = LogTimeFor::first();
        $this->time = $this->data['logTime']->time ? Carbon::createFromFormat('H:i:s', $this->data['logTime']->time)->format(company()->time_format) : '';
        $this->roles = Role::where('name', '<>', 'client')->get();
        $this->dailyReportRoles = json_decode($this->data['logTime']->daily_report_roles);

        return view('log-time-settings.index', $this->data);
    }

    /**
     * @param UpdateTimeLog $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(UpdateTimeLog $request)
    {
        $logTime = LogTimeFor::first();

        if ($request->has('log_time_for')) {
            $logTime->log_time_for = $request->log_time_for;
        }

        if ($request->has('auto_timer_stop')) {
            $logTime->auto_timer_stop = $request->auto_timer_stop;
        }

        if ($request->has('approval_required')) {
            $logTime->approval_required = $request->approval_required;
        }

        if ($request->has('tracker_reminder') && $request->time) {
            $logTime->tracker_reminder = $request->tracker_reminder;
            $logTime->time = Carbon::createFromFormat($this->company->time_format, $request->time)->format('H:i:s');
        }

        if ($request->has('timelog_report')) {
            $logTime->timelog_report = $request->timelog_report;
            $logTime->daily_report_roles = json_encode($request->daily_report_roles);
        }

        $logTime->save();
        session()->forget('time_log_setting');

        return Reply::success(__('messages.updateSuccess'));

    }

}
