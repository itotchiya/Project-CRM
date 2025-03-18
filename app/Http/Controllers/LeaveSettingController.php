<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Designation;
use App\Models\LeaveSetting;
use App\Models\LeaveType;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class LeaveSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.leaveTypeSettings';
        $this->activeSettingMenu = 'leave_settings';

        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_leave_setting') == 'all' && in_array('leaves', user_modules())));
            return $next($request);
        });
    }

    public function index()
    {
        $this->leaveTypes = LeaveType::withCount('leaves')->get();
    

        $tab = request('tab');

        switch ($tab) {
        case 'general':
            $this->leavePermission = LeaveSetting::first();
            $this->view = 'leave-settings.ajax.general';
                break;
        case 'archive':
            $this->archiveleaveTypes = LeaveType::onlyTrashed()->get();
            $this->departments = Team::all();
            $this->designations = Designation::all();
            $this->view = 'leave-settings.ajax.archive';
                break;
        default:
            $this->departments = Team::all();
            $this->designations = Designation::all();
            $this->view = 'leave-settings.ajax.type';
                break;
        }

        $this->activeTab = $tab ?: 'type';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('leave-settings.index', $this->data);
    }

    public function store(Request $request)
    {
        $setting = company();
        $setting->leaves_start_from = $request->leaveCountFrom;
        $setting->year_starts_from = $request->yearStartFrom;
        $setting->save();

        Artisan::call('app:recalculate-leaves-quotas ' . $setting->id);


        return Reply::success(__('messages.updateSuccess'));
    }

    public function changePermission(Request $request)
    {
        $permission = LeaveSetting::findOrFail($request->id);
        $permission->manager_permission = $request->value;
        $permission->update();

        return Reply::success(__('messages.updateSuccess'));
    }

}
