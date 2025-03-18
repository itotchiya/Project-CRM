<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Project;
use App\Models\ProjectSetting;
use App\Models\ProjectCategory;
use App\Models\ProjectStatusSetting;
use App\Http\Requests\StoreStatusSettingRequest;
use App\Http\Requests\Project\StoreProjectCategory;
use App\Http\Requests\ProjectSetting\UpdateProjectSetting;

class ProjectSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projectSettings';
        $this->activeSettingMenu = 'project_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_project_setting') == 'all' && in_array('projects', user_modules())));

            return $next($request);
        });
    }

    public function index()
    {

        $tab = request('tab');

        switch ($tab) {
        case 'status':
            $this->projectStatusSetting = ProjectStatusSetting::all();
            $this->view = 'project-settings.ajax.status';
            break;
        case 'category':
            $this->projectCategory = ProjectCategory::all();
            $this->view = 'project-settings.ajax.category';
            break;
        default:
            $this->projectSetting = ProjectSetting::first();
            $this->view = 'project-settings.ajax.sendReminder';
            break;
        }

        $this->activeTab = $tab ?: 'sendReminder';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('project-settings.index', $this->data);
    }

    public function create()
    {
        return view('project-settings.create-project-status-settings-modal', $this->data);
    }

    public function store(StoreStatusSettingRequest $request)
    {
        $projectStatusSetting = new ProjectStatusSetting();
        $projectStatusSetting->status_name = $request->name;
        $projectStatusSetting->color = $request->status_color;
        $projectStatusSetting->status = $request->status;
        $projectStatusSetting->default_status = ProjectStatusSetting::INACTIVE;
        $projectStatusSetting->save();

        return Reply::success(__('messages.recordSaved'));
    }

    public function edit($id)
    {
        $this->projectStatusSetting = ProjectStatusSetting::findOrfail($id);

        return view('project-settings.edit', $this->data);
    }

    public function statusUpdate(StoreStatusSettingRequest $request, $id)
    {
        $projectStatusSetting = ProjectStatusSetting::findOrFail($id);

        $projectStatusSetting->status_name = $request->name;
        $projectStatusSetting->color = $request->status_color;
        $projectStatusSetting->status = $request->status;

        $projectStatusSetting->update();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function changeStatus($id)
    {
        $projectStatusSetting = ProjectStatusSetting::findOrFail($id);

        $projectStatusSetting->status = request()->status;

        $projectStatusSetting->update();

        return Reply::success(__('messages.recordSaved'));
    }

    public function setDefault()
    {

        ProjectStatusSetting::where('id', request()->id)->update(['default_status' => ProjectStatusSetting::ACTIVE]);
        ProjectStatusSetting::where('id', '<>', request()->id)->update(['default_status' => ProjectStatusSetting::INACTIVE]);

        return Reply::success(__('messages.updateSuccess'));
    }

    public function update(UpdateProjectSetting $request, $id)
    {
        $projectSetting = ProjectSetting::findOrFail($id);

        $projectSetting->send_reminder = $request->send_reminder ? 'yes' : 'no';
        $projectSetting->remind_time = $request->remind_time;
        $projectSetting->remind_type = $request->remind_type;

        $remindTo = [];

        if ($request->remind_to == 'all') {
            $remindTo = [ProjectSetting::REMIND_TO_MEMBERS, ProjectSetting::REMIND_TO_ADMINS];
        }

        if ($request->remind_to == 'members') {
            $remindTo = [ProjectSetting::REMIND_TO_MEMBERS];
        }

        if ($request->remind_to == 'admins') {
            $remindTo = [ProjectSetting::REMIND_TO_ADMINS];
        }


        $projectSetting->remind_to = $remindTo;
        $projectSetting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $projectStatusSetting = ProjectStatusSetting::findOrFail($id);
        $default = ProjectStatusSetting::where('default_status', ProjectStatusSetting::ACTIVE)->first();

        Project::where('status', $projectStatusSetting->status_name)->update(['status' => $default->status_name]);

        ProjectStatusSetting::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function createCategory()
    {
        $this->addPermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        return view('project-settings.create-project-category-settings-modal', $this->data);
    }

    public function saveProjectCategory(StoreProjectCategory $request)
    {
        $this->addPermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $category = new ProjectCategory();
        $category->category_name = $request->category_name;
        $category->save();

        return Reply::success(__('messages.recordSaved'));
    }

}
