<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\TemplateTasks\StoreTask;
use App\Models\ProjectTemplate;
use App\Models\ProjectTemplateTask;
use App\Models\ProjectTemplateTaskUser;
use App\Models\TaskCategory;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\TaskLabelList;
use App\Models\TaskSetting;

class ProjectTemplateTaskController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projectTemplateTask';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('projects', $this->user->modules));

            return $next($request);
        });
    }

    public function index()
    {
        return redirect()->route('project-template.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $this->manageProjectTemplatePermission = user()->permission('manage_project_template');
        abort_403(!in_array($this->manageProjectTemplatePermission, ['all', 'added']));

        $this->pageTitle = __('app.menu.addProjectTemplate');
        $this->template = ProjectTemplate::findOrFail($request->project_id);
        $this->categories = TaskCategory::all();
        $this->labels = TaskLabelList::whereNull('project_id')->get();
        $this->project = request('project_id') ? ProjectTemplate::with('projectMembers')->findOrFail(request('project_id')) : null;

        if (!is_null($this->project)) {
            $this->employees = $this->project->projectMembers;

        }
        else {
            $this->employees = User::allEmployees(null, true);
        }

        $this->view = 'project-templates.task.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('project-templates.task.create', $this->data);

    }

    /**
     * @param StoreTask $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreTask $request)
    {
        $task = new ProjectTemplateTask();
        $task->heading = $request->heading;

        if ($request->description != '') {
            $task->description = trim_editor($request->description);
        }

        $task->project_template_id = $request->template_id;
        $task->project_template_task_category_id = $request->category_id;
        $task->priority = $request->priority;

        if ($request->has('task_labels')) {
            $task->task_labels = implode(',', $request->task_labels);
        }

        $task->save();

        if ($request->user_id) {
            foreach ($request->user_id as $key => $value) {
                ProjectTemplateTaskUser::create([
                    'user_id' => $value,
                    'project_template_task_id' => $task->id
                ]);
            }
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('project-template.show', $request->template_id . '?tab=tasks'), 'taskID' => $task->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->task = ProjectTemplateTask::with(['category'])->findOrFail($id);

        $manageProjectTemplatePermission = user()->permission('manage_project_template');
        $viewProjectTemplatePermission = user()->permission('view_project_template');

        abort_403(!in_array($manageProjectTemplatePermission, ['all', 'added', 'both']) && !in_array($viewProjectTemplatePermission, ['all']));

        $this->pageTitle = __('app.task') . ' # ' . $this->task->id;
        $this->taskSettings = TaskSetting::first();
        $this->taskLabelList = TaskLabelList::whereNull('project_id')->get();
        $this->tab = 'project-templates.task.ajax.sub_tasks';
        $this->view = 'project-templates.task.ajax.show';

        if (request()->ajax()) {

            if (request('json')) {
                return $this->returnAjax($this->tab);
            }

            return $this->returnAjax($this->view);
        }

        return view('project-templates.task.create', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->task = ProjectTemplateTask::findOrFail($id);
        $this->manageProjectTemplatePermission = user()->permission('manage_project_template');
        abort_403(!in_array($this->manageProjectTemplatePermission, ['all', 'added']));

        $this->pageTitle = __('app.update') . ' ' . __('app.project');
        $this->labels = TaskLabelList::whereNull('project_id')->get();
        $this->categories = TaskCategory::all();
        $this->template = ProjectTemplate::findOrFail($this->task->project_template_id);

        $this->selectedLabels = explode(',', $this->task->task_labels);

        $this->project = request('project_id') ? ProjectTemplate::with('projectMembers')->findOrFail(request('project_id')) : null;

        if (!is_null($this->project)) {
            $this->employees = $this->project->projectMembers;
        }else {
            $this->employees = User::allEmployees(null, true);
        }

        $this->view = 'project-templates.task.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('project-templates.task.create', $this->data);

    }

    /**
     * @param StoreTask $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreTask $request, $id)
    {
        $task = ProjectTemplateTask::findOrFail($id);
        $task->heading = $request->heading;

        if ($request->description != '') {
            $task->description = trim_editor($request->description);
        }

        $task->project_template_task_category_id = $request->category_id;
        $task->priority = $request->priority;

        if ($request->has('task_labels')) {
            $task->task_labels = implode(',', $request->task_labels);
        }

        $task->save();

        ProjectTemplateTaskUser::where('project_template_task_id', $task->id)->delete();

        if ($request->user_id) {
            foreach ($request->user_id as $key => $value) {
                ProjectTemplateTaskUser::create(
                    [
                        'user_id' => $value,
                        'project_template_task_id' => $task->id
                    ]
                );
            }
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('project-template.show', $task->project_template_id . '?tab=tasks')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ProjectTemplateTask::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

}
