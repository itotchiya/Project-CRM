<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Leave;
use App\Models\Pinned;
use App\Models\Project;
use App\Models\SubTask;
use App\Models\TaskFile;
use App\Models\TaskUser;
use App\Http\Requests\Tasks\ActionTask;
use App\Models\TaskComment;
use App\Models\BaseModel;
use App\Models\TaskLabel;
use App\Models\SubTaskFile;
use App\Models\TaskSetting;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use App\Models\TaskLabelList;
use App\Models\ProjectTimeLog;
use App\Models\TaskboardColumn;
use App\Traits\ProjectProgress;
use App\Models\ProjectMilestone;
use App\Events\TaskReminderEvent;
use App\DataTables\TasksDataTable;
use App\DataTables\WaitingForApprovalDataTable;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectTimeLogBreak;
use App\Http\Requests\Tasks\StoreTask;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\Tasks\UpdateTask;
use App\Events\TaskEvent;

class TaskController extends AccountBaseController
{

    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.tasks';
        $this->middleware(
            function ($request, $next) {
                abort_403(!in_array('tasks', $this->user->modules));

                return $next($request);
            }
        );
    }

    public function index(TasksDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_tasks');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->assignedTo = request()->assignedTo;

            if (request()->has('assignee') && request()->assignee == 'me') {
                $this->assignedTo = user()->id;
            }

            $this->projects = Project::allProjects();

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
            }

            $this->employees = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->taskCategories = TaskCategory::all();
            $this->taskLabels = TaskLabelList::all();
            $this->milestones = ProjectMilestone::all();

            $taskBoardColumn = TaskboardColumn::waitingForApprovalColumn();

            $projectIds = Project::where('project_admin', user()->id)->pluck('id');

            if (!in_array('admin', user_roles()) && (in_array('employee', user_roles()) && $projectIds->isEmpty())) {
                $user = User::findOrFail(user()->id);
                $this->waitingApprovalCount = $user->tasks()->where('board_column_id', $taskBoardColumn->id)->where('company_id', company()->id)->count();
            }elseif(!in_array('admin', user_roles()) && (in_array('employee', user_roles()) && !$projectIds->isEmpty())) {
                $this->waitingApprovalCount = Task::whereIn('project_id', $projectIds)->where('board_column_id', $taskBoardColumn->id)->where('company_id', company()->id)->count();
            }else{
                $this->waitingApprovalCount = Task::where('board_column_id', $taskBoardColumn->id)->where('company_id', company()->id)->count();
            }
        }

        return $dataTable->render('tasks.index', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        case 'milestone':
            $this->changeMilestones($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_tasks') != 'all');

        $ids = explode(',', $request->row_ids);

        Task::whereIn('id', $ids)->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_tasks') != 'all');

        $taskBoardColumn = TaskboardColumn::findOrFail(request()->status);

        // Update tasks based on the requested status
        $taskIds = explode(',', $request->row_ids);

        if ($taskBoardColumn && $taskBoardColumn->slug == 'completed') {
            Task::whereIn('id', $taskIds)->update([
                'status' => 'completed',
                'board_column_id' => $request->status,
                'completed_on' => now()->format('Y-m-d')
            ]);
        }
        else {
            Task::whereIn('id', $taskIds)->update(['board_column_id' => $request->status]);
        }

    }

    public function changeMilestones($request)
    {
        abort_403(user()->permission('edit_tasks') != 'all');

        $taskIds = explode(',', $request->row_ids);

        Task::whereIn('id', $taskIds)->update([
            'milestone_id' => $request->milestone
        ]);
    }

    public function changeStatus(Request $request)
    {
        $taskId = $request->taskId;
        $status = $request->status;

        $task = Task::withTrashed()->with('project', 'users')->findOrFail($taskId);

        $taskUsers = $task->users->pluck('id')->toArray();

        $this->editPermission = user()->permission('edit_tasks');
        $this->changeStatusPermission = user()->permission('change_status');
        abort_403(
            !(
                $this->changeStatusPermission == 'all'
                || ($this->changeStatusPermission == 'added' && $task->added_by == user()->id)
                || ($this->changeStatusPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($this->changeStatusPermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
                || ($task->project && $task->project->project_admin == user()->id)
            )
        );

        $taskBoardColumn = TaskboardColumn::where('slug', $status)->first();
        $task->board_column_id = $taskBoardColumn->id;

        if ($task->status === 'completed' && $status !== 'completed') {
            $task->approval_send = 0; // Reset approval_send to 0
        }

        if ($taskBoardColumn->slug == 'completed') {
            $task->status = 'completed';
            $task->completed_on = now()->format('Y-m-d');
        }
        else {
            $task->completed_on = null;
        }

        if ($task->trashed()) {
            $task->saveQuietly();
        }
        else {
            $task->save();
        }


        if ($task->project_id != null) {

            if ($task->project->calculate_task_progress == 'true') {
                // Calculate project progress if enabled
                $this->calculateProjectProgress($task->project_id, 'true');
            }
        }

        $this->selfActiveTimer = ProjectTimeLog::selfActiveTimer();

        $clockHtml = view('sections.timer_clock', $this->data)->render();

        return Reply::successWithData(__('messages.updateSuccess'), ['clockHtml' => $clockHtml]);

    }

    public function milestoneChange(Request $request)
    {
        $editTaskPermission = user()->permission('edit_tasks');
        $editMilestonePermission = user()->permission('edit_project_milestones');

        $taskId = $request->taskId;
        $milestoneId = $request->milestone_id;

        $task = Task::withTrashed()->with('project', 'users')->findOrFail($taskId);
        $taskUsers = $task->users->pluck('id')->toArray();

        abort_403(
            !(
                ($editTaskPermission == 'all'
                || ($editTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($editTaskPermission == 'added' && $task->added_by == user()->id)
                || ($task->project && ($task->project->project_admin == user()->id))
                || ($editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
                || ($editTaskPermission == 'owned' && (in_array('client', user_roles()) && $task->project && ($task->project->client_id == user()->id)))
                || ($editTaskPermission == 'both' && (in_array('client', user_roles()) && ($task->project && ($task->project->client_id == user()->id)) || $task->added_by == user()->id))
                ) &&(
                    $editMilestonePermission == 'all'
                    || ($editMilestonePermission == 'added' && $task->added_by == user()->id)
                    || ($editMilestonePermission == 'owned' && in_array(user()->id, $taskUsers))
                    || ($editMilestonePermission == 'owned' && (in_array('client', user_roles()) && $task->project && ($task->project->client_id == user()->id)))
                )
            )
        );

        $task->milestone_id = $milestoneId;
        $task->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy(Request $request, $id)
    {
        $task = Task::with('project')->findOrFail($id);

        $this->deletePermission = user()->permission('delete_tasks');

        $taskUsers = $task->users->pluck('id')->toArray();

        abort_403(
            !($this->deletePermission == 'all'
                || ($this->deletePermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($task->project && ($task->project->project_admin == user()->id))
                || ($this->deletePermission == 'added' && $task->added_by == user()->id)
                || ($this->deletePermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
                || ($this->deletePermission == 'owned' && (in_array('client', user_roles()) && $task->project && ($task->project->client_id == user()->id)))
                || ($this->deletePermission == 'both' && (in_array('client', user_roles()) && ($task->project && ($task->project->client_id == user()->id)) || $task->added_by == user()->id))
            )
        );

        // If it is recurring and allowed by user to delete all its recurring tasks
        if ($request->has('recurring') && $request->recurring == 'yes') {
            Task::where('recurring_task_id', $id)->delete();
        }

        // Delete current task
        $task->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('app.addTask');

        $this->addPermission = user()->permission('add_tasks');
        $this->projectShortCode = '';
        $this->project = request('task_project_id') ? Project::with('projectMembers')->findOrFail(request('task_project_id')) : null;

        if (is_null($this->project) || ($this->project->project_admin != user()->id)) {
            abort_403(!in_array($this->addPermission, ['all', 'added']));
        }

        $this->task = (request()['duplicate_task']) ? Task::with('users', 'label', 'project')->findOrFail(request()['duplicate_task'])->withCustomFields() : null;
        $this->selectedLabel = TaskLabel::where('task_id', request()['duplicate_task'])->get()->pluck('label_id')->toArray();
        $this->projectMember = TaskUser::where('task_id', request()['duplicate_task'])->get()->pluck('user_id')->toArray();

        $this->projects = Project::allProjects(true);

        $this->taskLabels = TaskLabelList::whereNull('project_id')->get();
        $this->projectID = request()->task_project_id;

        if (request('task_project_id')) {
            $project = Project::findOrFail(request('task_project_id'));
            $this->projectShortCode = $project->project_short_code;
            $this->taskLabels = TaskLabelList::where('project_id', request('task_project_id'))->orWhere('project_id', null)->get();
            $this->milestones = ProjectMilestone::where('project_id', request('task_project_id'))->whereNot('status', 'complete')->get();
        }
        else {
            if ($this->task && $this->task->project) {
                $this->milestones = $this->task->project->incompleteMilestones;
            }
            else {
                $this->milestones = collect([]);
            }
        }

        $this->columnId = request('column_id');
        $this->categories = TaskCategory::all();

        $this->taskboardColumns = TaskboardColumn::orderBy('priority', 'asc')->get();
        $completedTaskColumn = TaskboardColumn::where('slug', '=', 'completed')->first();

        if (request()->has('default_assign') && request('default_assign') != '') {
            $this->defaultAssignee = request('default_assign');
        }

        $this->dependantTasks = $completedTaskColumn ? Task::where('board_column_id', '<>', $completedTaskColumn->id)
            ->where('project_id', $this->projectID)
            ->whereNotNull('due_date')->get() : [];

        $this->allTasks = $completedTaskColumn ? Task::where('board_column_id', '<>', $completedTaskColumn->id)->whereNotNull('due_date')->get() : [];

        if (!is_null($this->project)) {
            if ($this->project->public) {
                $this->employees = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));

            }
            else {

                $this->employees = $this->project->projectMembers;
            }
        }
        else if (!is_null($this->task) && !is_null($this->task->project_id)) {
            if ($this->task->project->public) {
                $this->employees = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));
            }
            else {

                $this->employees = $this->task->project->projectMembers;
            }
        }
        else {
            if (in_array('client', user_roles())) {
                $this->employees = collect([]); // Do not show all employees to client

            }
            else {
                $this->employees = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));
            }

        }

        $task = new Task();

        $getCustomFieldGroupsWithFields = $task->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        $this->view = 'tasks.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('tasks.create', $this->data);
    }

    // The function is called for duplicate code also
    public function store(StoreTask $request)
    {
        $project = request('project_id') ? Project::findOrFail(request('project_id')) : null;

        if (is_null($project) || ($project->project_admin != user()->id)) {
            $this->addPermission = user()->permission('add_tasks');
            abort_403(!in_array($this->addPermission, ['all', 'added']));
        }

        DB::beginTransaction();
        $ganttTaskArray = [];
        $gantTaskLinkArray = [];

        $taskBoardColumn = TaskboardColumn::where('slug', 'incomplete')->first();
        $task = new Task();
        $task->heading = $request->heading;
        $task->description = trim_editor($request->description);
        $dueDate = ($request->has('without_duedate')) ? null : Carbon::createFromFormat(company()->date_format, $request->due_date);
        $task->start_date = Carbon::createFromFormat(company()->date_format, $request->start_date);
        $task->due_date = $dueDate;
        $task->project_id = $request->project_id;
        $task->task_category_id = $request->category_id;
        $task->priority = $request->priority;
        $task->board_column_id = $taskBoardColumn->id;

        if ($request->has('dependent') && $request->has('dependent_task_id') && $request->dependent_task_id != '') {
            $dependentTask = Task::findOrFail($request->dependent_task_id);

            if (!is_null($dependentTask->due_date) && !is_null($dueDate) && $dependentTask->due_date->greaterThan($dueDate)) {
                /* @phpstan-ignore-line */
                return Reply::error(__('messages.taskDependentDate'));
            }

            $task->dependent_task_id = $request->dependent_task_id;
        }

        $task->is_private = $request->has('is_private') ? 1 : 0;
        $task->billable = $request->has('billable') && $request->billable ? 1 : 0;
        $task->estimate_hours = $request->estimate_hours;
        $task->estimate_minutes = $request->estimate_minutes;

        if ($request->board_column_id) {
            $task->board_column_id = $request->board_column_id;
        }

        $waitingApprovalTaskBoardColumn = TaskboardColumn::waitingForApprovalColumn();
        if($request->board_column_id == $waitingApprovalTaskBoardColumn->id){
            $task->approval_send = 1;
        }else{
            $task->approval_send = 0;
        }

        if ($request->milestone_id != '') {
            $task->milestone_id = $request->milestone_id;
        }

        // Add repeated task
        $task->repeat = $request->repeat ? 1 : 0;

        if ($request->has('repeat')) {
            $task->repeat_count = $request->repeat_count;
            $task->repeat_type = $request->repeat_type;
            $task->repeat_cycles = $request->repeat_cycles;
        }

        if ($project) {
            $projectLastTaskCount = Task::projectTaskCount($project->id);

            if (isset($project->project_short_code)) {
                $task->task_short_code = $project->project_short_code . '-' . $this->getTaskShortCode($project->project_short_code, $projectLastTaskCount);
            }
            else{
                $task->task_short_code = $projectLastTaskCount + 1;
            }
        }

        $task->save();

        // Save labels

        $task->labels()->sync($request->task_labels);


        if (!is_null($request->taskId)) {

            $taskExists = TaskFile::where('task_id', $request->taskId)->get();

            if ($taskExists) {
                foreach ($taskExists as $taskExist) {
                    $file = new TaskFile();
                    $file->user_id = $taskExist->user_id;
                    $file->task_id = $task->id;

                    $fileName = Files::generateNewFileName($taskExist->filename);

                    Files::copy(TaskFile::FILE_PATH . '/' . $taskExist->task_id . '/' . $taskExist->hashname, TaskFile::FILE_PATH . '/' . $task->id . '/' . $fileName);

                    $file->filename = $taskExist->filename;
                    $file->hashname = $fileName;
                    $file->size = $taskExist->size;
                    $file->save();


                    $this->logTaskActivity($task->id, $this->user->id, 'fileActivity', $task->board_column_id);
                }
            }


            $subTask = SubTask::with(['files'])->where('task_id', $request->taskId)->get();


            if ($subTask) {
                foreach ($subTask as $subTasks) {
                    $subTaskData = new SubTask();
                    $subTaskData->title = $subTasks->title;
                    $subTaskData->task_id = $task->id;
                    $subTaskData->description = trim_editor($subTasks->description);

                    if ($subTasks->start_date != '' && $subTasks->due_date != '') {
                        $subTaskData->start_date = $subTasks->start_date;
                        $subTaskData->due_date = $subTasks->due_date;
                    }

                    $subTaskData->assigned_to = $subTasks->assigned_to;

                    $subTaskData->save();

                    if ($subTasks->files) {
                        foreach ($subTasks->files as $fileData) {
                            $file = new SubTaskFile();
                            $file->user_id = $fileData->user_id;
                            $file->sub_task_id = $subTaskData->id;

                            $fileName = Files::generateNewFileName($fileData->filename);

                            Files::copy(SubTaskFile::FILE_PATH . '/' . $fileData->sub_task_id . '/' . $fileData->hashname, SubTaskFile::FILE_PATH . '/' . $subTaskData->id . '/' . $fileName);

                            $file->filename = $fileData->filename;
                            $file->hashname = $fileName;
                            $file->size = $fileData->size;
                            $file->save();
                        }
                    }
                }
            }
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $task->updateCustomFieldData($request->custom_fields_data);
        }

        // For gantt chart
        if ($request->page_name && !is_null($task->due_date) && $request->page_name == 'ganttChart') {
            $task = Task::find($task->id);
            $parentGanttId = $request->parent_gantt_id;

            /* @phpstan-ignore-next-line */

            $taskDuration = $task->due_date->diffInDays($task->start_date);
            /* @phpstan-ignore-line */
            $taskDuration = $taskDuration + 1;

            $ganttTaskArray[] = [
                'id' => $task->id,
                'text' => $task->heading,
                'start_date' => $task->start_date->format('Y-m-d'), /* @phpstan-ignore-line */
                'duration' => $taskDuration,
                'parent' => $parentGanttId,
                'taskid' => $task->id
            ];

            $gantTaskLinkArray[] = [
                'id' => 'link_' . $task->id,
                'source' => $task->dependent_task_id != '' ? $task->dependent_task_id : $parentGanttId,
                'target' => $task->id,
                'type' => $task->dependent_task_id != '' ? 0 : 1
            ];
        }


        DB::commit();

        if (request()->add_more == 'true') {
            unset($request->project_id);
            $html = $this->create();

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true, 'taskID' => $task->id]);
        }

        if ($request->page_name && $request->page_name == 'ganttChart') {

            return Reply::successWithData(
                'messages.recordSaved',
                [
                    'tasks' => $ganttTaskArray,
                    'links' => $gantTaskLinkArray
                ]
            );
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('tasks.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl, 'taskID' => $task->id]);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $editTaskPermission = user()->permission('edit_tasks');
        $this->task = Task::with('users', 'label', 'project',)->findOrFail($id)->withCustomFields();
        $this->taskUsers = $taskUsers = $this->task->users->pluck('id')->toArray();
        abort_403(
            !($editTaskPermission == 'all'
                || ($editTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($editTaskPermission == 'added' && $this->task->added_by == user()->id)
                || ($this->task->project && ($this->task->project->project_admin == user()->id))
                || ($editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $this->task->added_by == user()->id))
                || ($editTaskPermission == 'owned' && (in_array('client', user_roles()) && $this->task->project && ($this->task->project->client_id == user()->id)))
                || ($editTaskPermission == 'both' && (in_array('client', user_roles()) && ($this->task->project && ($this->task->project->client_id == user()->id)) || $this->task->added_by == user()->id))
            )
        );

        $getCustomFieldGroupsWithFields = $this->task->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }


        $this->pageTitle = __('modules.tasks.updateTask');
        $this->labelIds = $this->task->label->pluck('label_id')->toArray();
        $this->projects = Project::allProjects(true);
        $this->categories = TaskCategory::all();
        $projectId = $this->task->project_id;

        if($projectId){
            $this->taskLabels = TaskLabelList::where('project_id', $projectId)->orWhereNull('project_id')->get();
        }else{
            $this->taskLabels = TaskLabelList::whereNull('project_id')->get();
        }

        $this->taskboardColumns = TaskboardColumn::orderBy('priority', 'asc')->get();
        $this->changeStatusPermission = user()->permission('change_status');
        $completedTaskColumn = TaskboardColumn::where('slug', '=', 'completed')->first();
        $this->waitingApprovalTaskBoardColumn = TaskboardColumn::waitingForApprovalColumn();
        if ($completedTaskColumn) {
            $this->allTasks = Task::where('board_column_id', '<>', $completedTaskColumn->id)->whereNotNull('due_date')->where('id', '!=', $id)->where('project_id', $projectId)->get();
        }
        else {
            $this->allTasks = [];
        }

        if ($this->task->project_id) {
            if ($this->task->project->public) {
                $this->employees = User::allEmployees(null, false, ($editTaskPermission == 'all' ? 'all' : null));

            }
            else {
                $this->employees = $this->task->project->projectMembersWithoutScope;
            }
        }
        else {
            if ($editTaskPermission == 'added' || $editTaskPermission == 'owned') {
                $this->employees = ((count($this->task->users) > 0) ? $this->task->users : User::allEmployees(null, true, ($editTaskPermission == 'all' ? 'all' : null)));

            }
            else {
                $this->employees = User::allEmployees(null, false, ($editTaskPermission == 'all' ? 'all' : null));
            }
        }


        $uniqueId = $this->task->task_short_code;
        // check if unuqueId contains -
        if (strpos($uniqueId, '-') !== false) {
            $uniqueId = explode('-', $uniqueId, 2);
            $this->projectUniId = $uniqueId[0];
            $this->taskUniId = $uniqueId[1];
        }
        else {
            $this->projectUniId = ($this->task->project_id != null) ? $this->task->project->project_short_code : null;
            $this->taskUniId = $uniqueId;
        }

        $userId = $this->task->users->pluck('id')->toArray();
        $startDate = $this->task->start_date;
        $dueDate = $this->task->due_date;
        $leaves = $this->leaves($userId, $startDate, $dueDate);

        if (!is_null($leaves)) {
            $data = [];

            foreach ($leaves as $key => $value) {
                $values = implode(', ', $value);
                $data[] = $key . __('modules.tasks.leaveOn') . ' ' . $values;
            }

            $this->leaveData = implode("\n", $data);
            /* @phpstan-ignore-line */

        }

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        $this->view = 'tasks.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('tasks.create', $this->data);

    }

    public function update(UpdateTask $request, $id)
    {
        $task = Task::with('users', 'label', 'project')->findOrFail($id)->withCustomFields();
        $editTaskPermission = user()->permission('edit_tasks');
        $taskUsers = $task->users->pluck('id')->toArray();

        abort_403(
            !($editTaskPermission == 'all'
                || ($editTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($editTaskPermission == 'added' && $task->added_by == user()->id)
                || ($task->project && ($task->project->project_admin == user()->id))
                || ($editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
                || ($editTaskPermission == 'owned' && (in_array('client', user_roles()) && $task->project && ($task->project->client_id == user()->id)))
                || ($editTaskPermission == 'both' && (in_array('client', user_roles()) && ($task->project && ($task->project->client_id == user()->id)) || $task->added_by == user()->id))
            )
        );

        $dueDate = ($request->has('without_duedate')) ? null : Carbon::createFromFormat(company()->date_format, $request->due_date);
        $task->heading = $request->heading;
        $task->description = trim_editor($request->description);
        $task->start_date = Carbon::createFromFormat(company()->date_format, $request->start_date);
        $task->due_date = $dueDate;
        $task->task_category_id = $request->category_id;
        $task->priority = $request->priority;


        if ($request->has('board_column_id')) {

            $task->board_column_id = $request->board_column_id;
            $task->approval_send = 0;
            $taskBoardColumn = TaskboardColumn::findOrFail($request->board_column_id);

            if ($taskBoardColumn->slug == 'completed') {
                $task->completed_on = now()->format('Y-m-d');
            }
            else {
                $task->completed_on = null;
            }
        }

        if($request->select_value == 'Waiting Approval'){

            $taskBoardColumn = TaskboardColumn::where('column_name', $request->select_value)->where('company_id', company()->id)->first();
            $task->board_column_id = $taskBoardColumn->id;
            $task->approval_send = 1;
        }

        $task->dependent_task_id = $request->has('dependent') && $request->has('dependent_task_id') && $request->dependent_task_id != '' ? $request->dependent_task_id : null;
        $task->is_private = $request->has('is_private') ? 1 : 0;
        $task->billable = $request->has('billable') && $request->billable ? 1 : 0;
        $task->estimate_hours = $request->estimate_hours;
        $task->estimate_minutes = $request->estimate_minutes;

        if ($request->project_id != '') {
            $task->project_id = $request->project_id;
            ProjectTimeLog::where('task_id', $id)->update(['project_id' => $request->project_id]);
        }
        else {
            $task->project_id = null;
        }

        if ($request->has('milestone_id')) {
            $task->milestone_id = $request->milestone_id;
        }

        if ($request->has('dependent') && $request->has('dependent_task_id') && $request->dependent_task_id != '') {
            $dependentTask = Task::findOrFail($request->dependent_task_id);

            if (!is_null($dependentTask->due_date) && !is_null($dueDate) && $dependentTask->due_date->greaterThan($dueDate)) {
                return Reply::error(__('messages.taskDependentDate'));
            }

            $task->dependent_task_id = $request->dependent_task_id;
        }

        // Add repeated task
        $task->repeat = $request->repeat ? 1 : 0;

        if ($request->has('repeat')) {
            $task->repeat_count = $request->repeat_count;
            $task->repeat_type = $request->repeat_type;
            $task->repeat_cycles = $request->repeat_cycles;
        }

        $task->load('project');

        $project = $task->project;

        if ($project && $task->isDirty('project_id')) {
            $projectLastTaskCount = Task::projectTaskCount($project->id);
            $task->task_short_code = $project->project_short_code . '-' . $this->getTaskShortCode($project->project_short_code, $projectLastTaskCount);
        }
        $task->save();

        // save labels
        $task->labels()->sync($request->task_labels);

        // To add custom fields data
        if ($request->custom_fields_data) {
            $task->updateCustomFieldData($request->custom_fields_data);
        }

        // Sync task users
        $task->users()->sync($request->user_id);

        if(!empty($request->user_id)){
            $newlyAssignedUserIds = array_diff($request->user_id, $taskUsers);
            if (!empty($newlyAssignedUserIds)) {
                $newUsers = User::whereIn('id', $newlyAssignedUserIds)->get();
                event(new TaskEvent($task, $newUsers, 'NewTask'));
            }
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('tasks.show', $id)]);
    }

    /**
     * @param $projectShortCode
     * @param $lastProjectCount
     * @return mixed
     */
    public function getTaskShortCode($projectShortCode, $lastProjectCount)
    {
        $task = Task::where('task_short_code', $projectShortCode . '-' . $lastProjectCount)->exists();

        if ($task) {
            return $this->getTaskShortCode($projectShortCode, $lastProjectCount + 1);
        }

        return $lastProjectCount;

    }

    public function show($id)
    {

        $viewTaskFilePermission = user()->permission('view_task_files');
        $viewSubTaskPermission = user()->permission('view_sub_tasks');
        $this->viewTaskCommentPermission = user()->permission('view_task_comments');
        $this->viewTaskNotePermission = user()->permission('view_task_notes');
        $this->viewUnassignedTasksPermission = user()->permission('view_unassigned_tasks');

        $this->task = Task::with(
            ['boardColumn', 'project', 'users', 'label', 'approvedTimeLogs', 'mentionTask',
                'approvedTimeLogs.user', 'approvedTimeLogs.activeBreak', 'comments','activeUsers',
                'comments.commentEmoji', 'comments.like', 'comments.dislike', 'comments.likeUsers',
                'comments.dislikeUsers', 'comments.user', 'subtasks.files', 'userActiveTimer',
                'files' => function ($q) use ($viewTaskFilePermission) {
                    if ($viewTaskFilePermission == 'added') {
                        $q->where('added_by', user()->id);
                    }
                },
                'subtasks' => function ($q) use ($viewSubTaskPermission) {
                    if ($viewSubTaskPermission == 'added') {
                        $q->where('added_by', user()->id);
                    }
                }]
        )
            ->withCount('subtasks', 'files', 'comments', 'activeTimerAll')
            ->findOrFail($id)->withCustomFields();


        $this->taskUsers = $taskUsers = $this->task->users->pluck('id')->toArray();

        $taskuserData = [];

        $usersData = $this->task->users;

        if ($this->task->createBy && !in_array($this->task->createBy->id, $taskUsers)) {
            $url = route('employees.show', [$this->task->createBy->user_id ?? $this->task->createBy->id]);
            $taskuserData[] = ['id' => $this->task->createBy->user_id ?? $this->task->createBy->id, 'value' => $this->task->createBy->user->name ?? $this->task->createBy->name, 'image' => $this->task->createBy->user->image_url ?? $this->task->createBy->image_url, 'link' => $url];
        }

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->user_id ?? $user->id]);
            $taskuserData[] = ['id' => $user->user_id ?? $user->id, 'value' => $user->user->name ?? $user->name, 'image' => $user->user->image_url ?? $user->image_url, 'link' => $url];

        }

        $this->taskuserData = $taskuserData;

        $this->taskSettings = TaskSetting::first();
        $viewTaskPermission = user()->permission('view_tasks');
        $mentionUser = $this->task->mentionTask->pluck('user_id')->toArray();

        $overrideViewPermission = false;

        if (request()->has('tab') && request('tab') === 'project') {
            $overrideViewPermission = true;
        }

        abort_403(
            !(
                $overrideViewPermission == true
                || $viewTaskPermission == 'all'
                || ($viewTaskPermission == 'added' && $this->task->added_by == user()->id)
                || ($viewTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
                || ($viewTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $this->task->added_by == user()->id))
                || ($viewTaskPermission == 'owned' && in_array('client', user_roles()) && $this->task->project_id && $this->task->project->client_id == user()->id)
                || ($viewTaskPermission == 'both' && in_array('client', user_roles()) && $this->task->project_id && $this->task->project->client_id == user()->id)
                || ($this->viewUnassignedTasksPermission == 'all' && in_array('employee', user_roles()))
                || ($this->task->project_id && $this->task->project->project_admin == user()->id)
                || ((!is_null($this->task->mentionTask)) && in_array(user()->id, $mentionUser))
            )

        );

        if (!$this->task->project_id || ($this->task->project_id && $this->task->project->project_admin != user()->id)) {

            abort_403($this->viewUnassignedTasksPermission == 'none' && count($taskUsers) == 0 && ((is_null($this->task->mentionTask)) && in_array(user()->id, $mentionUser)));

        }

        if($this->task->task_short_code){
            $this->pageTitle = __('app.task') . ' # ' . $this->task->task_short_code;
        }else{
            $this->pageTitle = __('app.task');
        }
        $this->status = TaskboardColumn::where('id', $this->task->board_column_id)->first();
        $getCustomFieldGroupsWithFields = $this->task->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }


        $this->employees = User::join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->leftJoin('project_time_logs', 'project_time_logs.user_id', '=', 'users.id')
            ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id');


        $this->employees = $this->employees->select(
            'users.name',
            'users.image',
            'users.id',
            'designations.name as designation_name'
        );

        $this->employees = $this->employees->where('project_time_logs.task_id', '=', $id);

        $this->employees = $this->employees->groupBy('project_time_logs.user_id')
            ->orderBy('users.name')
            ->get();

        $this->breakMinutes = ProjectTimeLogBreak::taskBreakMinutes($this->task->id);

        // Add Gitlab task details if available
        if (module_enabled('Gitlab')) {
            if (in_array('gitlab', user_modules()) && !is_null($this->task->project_id)) {

                /** @phpstan-ignore-next-line */
                $this->gitlabSettings = \Modules\Gitlab\Entities\GitlabSetting::where('user_id', user()->id)->first();

                if (!$this->gitlabSettings) {
                    /** @phpstan-ignore-next-line */
                    $this->gitlabSettings = \Modules\Gitlab\Entities\GitlabSetting::whereNull('user_id')->first();
                }

                if ($this->gitlabSettings) {
                    /** @phpstan-ignore-next-line */
                    Config::set('gitlab.connections.main.token', $this->gitlabSettings->personal_access_token);
                    /** @phpstan-ignore-next-line */
                    Config::set('gitlab.connections.main.url', $this->gitlabSettings->gitlab_url);

                    /** @phpstan-ignore-next-line */
                    $gitlabProject = \Modules\Gitlab\Entities\GitlabProject::where('project_id', $this->task->project_id)->first();
                    /** @phpstan-ignore-next-line */
                    $gitlabTask = \Modules\Gitlab\Entities\GitlabTask::where('task_id', $id)->first();

                    if ($gitlabTask) {
                        /** @phpstan-ignore-next-line */
                        $gitlabIssue = \GrahamCampbell\GitLab\Facades\GitLab::issues()->all(intval($gitlabProject->gitlab_project_id), ['iids' => [intval($gitlabTask->gitlab_task_iid)]]);

                        if ($gitlabIssue) {
                            $this->gitlabIssue = $gitlabIssue[0];
                        }
                    }
                }
            }
        }

        $tab = request('view');

        switch ($tab) {
        case 'sub_task':
            $this->tab = 'tasks.ajax.sub_tasks';
            break;
        case 'comments':
            abort_403($this->viewTaskCommentPermission == 'none');

            $this->tab = 'tasks.ajax.comments';
            break;
        case 'notes':
            abort_403($this->viewTaskNotePermission == 'none');
            $this->tab = 'tasks.ajax.notes';
            break;
        case 'history':
            $this->tab = 'tasks.ajax.history';
            break;
        case 'time_logs':
            abort_403(!in_array('timelogs', user_modules()));
            $this->tab = 'tasks.ajax.timelogs';
            break;
        default:
            if ($this->taskSettings->files == 'yes' && in_array('client', user_roles())) {
                $this->tab = 'tasks.ajax.files';
            }
            elseif ($this->taskSettings->sub_task == 'yes' && in_array('client', user_roles())) {
                $this->tab = 'tasks.ajax.sub_tasks';
            }
            elseif ($this->taskSettings->comments == 'yes' && in_array('client', user_roles())) {
                abort_403($this->viewTaskCommentPermission == 'none');
                $this->tab = 'tasks.ajax.comments';
            }
            elseif ($this->taskSettings->time_logs == 'yes' && in_array('client', user_roles())) {
                abort_403($this->viewTaskNotePermission == 'none');
                $this->tab = 'tasks.ajax.timelogs';
            }
            elseif ($this->taskSettings->notes == 'yes' && in_array('client', user_roles())) {
                abort_403($this->viewTaskNotePermission == 'none');
                $this->tab = 'tasks.ajax.notes';
            }
            elseif ($this->taskSettings->history == 'yes' && in_array('client', user_roles())) {
                abort_403($this->viewTaskNotePermission == 'none');
                $this->tab = 'tasks.ajax.history';
            }
            elseif (!in_array('client', user_roles())) {
                $this->tab = 'tasks.ajax.files';
            }
            break;
        }

        if (request()->ajax()) {
            $view = request('json') ? $this->tab : 'tasks.ajax.show';

            return $this->returnAjax($view);
        }


        $this->view = 'tasks.ajax.show';

        return view('tasks.create', $this->data);

    }

    public function storePin(Request $request)
    {
        $pinned = new Pinned();
        $pinned->task_id = $request->task_id;
        $pinned->project_id = $request->project_id;
        $pinned->save();

        return Reply::success(__('messages.pinnedSuccess'));
    }

    public function destroyPin(Request $request, $id)
    {
        $type = ($request->type == 'task') ? 'task_id' : 'project_id';

        Pinned::where($type, $id)->where('user_id', user()->id)->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function checkTask($taskID)
    {
        $task = Task::withTrashed()->findOrFail($taskID);
        $subTask = SubTask::where(['task_id' => $taskID, 'status' => 'incomplete'])->count();

        return Reply::dataOnly(['taskCount' => $subTask, 'lastStatus' => $task->boardColumn->slug]);
    }

    public function sendApproval(Request $request){

        $task = Task::findOrFail($request->taskId);
        $taskBoardColumn = TaskboardColumn::where('slug', 'waiting_approval')->first();

        $task->approval_send = $request->isApproval ?? 0;
        $task->board_column_id = $taskBoardColumn->id;
        $task->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function waitingApproval(WaitingForApprovalDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_tasks');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->assignedTo = request()->assignedTo;

            if (request()->has('assignee') && request()->assignee == 'me') {
                $this->assignedTo = user()->id;
            }

            $this->projects = Project::allProjects();

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
            }

            $this->employees = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->taskCategories = TaskCategory::all();
            $this->taskLabels = TaskLabelList::all();
            $this->milestones = ProjectMilestone::all();

            $taskBoardColumn = TaskboardColumn::waitingForApprovalColumn();

            $projectIds = Project::where('project_admin', user()->id)->pluck('id');

            if (!in_array('admin', user_roles()) && (in_array('employee', user_roles()) && $projectIds->isEmpty())) {
                $user = User::findOrFail(user()->id);
                $this->waitingApprovalCount = $user->tasks()->where('board_column_id', $taskBoardColumn->id)->count();
            }elseif(!in_array('admin', user_roles()) && (in_array('employee', user_roles()) && !$projectIds->isEmpty())) {
                $this->waitingApprovalCount = Task::whereIn('project_id', $projectIds)->where('board_column_id', $taskBoardColumn->id)->count();
            }else{
                $this->waitingApprovalCount = Task::where('board_column_id', $taskBoardColumn->id)->count();
            }
        }

        return $dataTable->render('tasks.waiting-approval', $this->data);
    }

    public function statusReason(Request $request){

        $this->taskStatus = $request->taskStatus;
        $this->taskId = $request->taskId;
        $this->userId = $request->userId;

        return view('tasks.status_reason_modal', $this->data);
    }

    public function storeStatusReason(ActionTask $request){

        $task = Task::findOrFail($request->taskId);
        $taskBoardColumn = TaskboardColumn::where('slug', $request->taskStatus)->first();
        $task->board_column_id = $taskBoardColumn->id;
        $task->approval_send = 0;
        $task->save();

        $comment = new TaskComment();
        $comment->comment = $request->reason;
        $comment->task_id = $request->taskId;
        $comment->user_id = user()->id;
        $comment->save();

        return Reply::dataOnly(['status' => 'success']);
    }

    public function clientDetail(Request $request)
    {
        $project = Project::with('client')->findOrFail($request->id);

        if (!is_null($project->client)) {
            $data = '<h5 class= "mb-2 f-13"> ' . __('modules.projects.projectClient') . '</h5>';
            $data .= view('components.client', ['user' => $project->client]);
            /* @phpstan-ignore-line */
        }
        else {
            $data = '<p> ' . __('modules.projects.projectDoNotHaveClient') . '</p>';
        }

        return Reply::dataOnly(['data' => $data]);
    }

    public function updateTaskDuration(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->start_date = Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
        $task->due_date = (!is_null($task->due_date)) ? Carbon::createFromFormat('d/m/Y', $request->end_date)->addDay()->format('Y-m-d') : null;
        $task->save();

        return Reply::success('messages.updateSuccess');
    }

    public function projectTasks($id)
    {
        if (request()->has('for_timelogs')) {
            $tasks = Task::projectLogTimeTasks($id);
            $options = BaseModel::options($tasks, null, 'heading');

            return Reply::dataOnly(['status' => 'success', 'data' => $options]);
        }

        $options = '<option value="">--</option>';

        $completedTaskColumn = TaskboardColumn::where('slug', '=', 'completed')->first();

        $tasks = Task::where('board_column_id', '<>', $completedTaskColumn->id)->whereNotNull('due_date');

        if ($id != 0 && $id != '') {
            $tasks = $tasks->where('project_id', $id);
        }

        $tasks = $completedTaskColumn ? $tasks->get() : [];

        foreach ($tasks as $item) {

            $options .= '<option  data-content="<div class=\'d-inline-block mr-1\'></div>  ' . $item->heading . ' ( Due date: ' . $item->due_date->format(company()->date_format) . ' ) " value="' . $item->id . '"> ' . $item->heading . '  ' . $item->due_date . ' </option>';
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    public function members($id)
    {
        $options = '<option value="">--</option>';

        if ($id != 0) {
            $members = Task::with('users')->findOrFail($id);

            foreach ($members->users as $item) {
                $self_select = (user() && user()->id == $item->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';
                if($item->status == 'active'){
                    $content = ( $item->status == 'deactive') ? "<span class='badge badge-pill badge-danger border align-center ml-2 px-2'>Inactive</span>" : '';
                    $options .= '<option  data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div>  ' . $item->name . '' . $self_select . '' . $content . '" value="' . $item->id .'"> ' . $item->name . ' </option>';
                }
            }
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    public function reminder()
    {
        $taskID = request()->id;
        $task = Task::with('users')->findOrFail($taskID);

        // Send  reminder notification to user
        event(new TaskReminderEvent($task));

        return Reply::success('messages.reminderMailSuccess');
    }

    public function checkLeaves()
    {
        $startDate = request()->start_date ? companyToYmd(request()->start_date) : null;
        $dueDate = request()->due_date ? companyToYmd(request()->due_date) : null;

        if (request()->start_date && request()->due_date && request()->user_id) {
            $data = $this->leaves(request()->user_id, $startDate, $dueDate);

            return reply::dataOnly(['data' => $data]);
        }
    }

    public function leaves($userIds, $startDate, $dueDate)
    {
        $leaveDates = [];

        foreach ($userIds as $userId) {
            $leaves = Leave::with('user')
                ->where('user_id', $userId)
                ->whereBetween('leave_date', [$startDate, $dueDate])
                ->get();

            foreach ($leaves as $leave) {
                $userName[] = $leave->user->name;
                $leaveDates[] = $leave->leave_date->format('d,M Y');
            }
        }

        if (isset($userName)) {
            $uniqueUser = array_unique($userName);
            $data = [];

            foreach ($uniqueUser as $name) {
                $data[$name] = [];

                foreach ($userName as $key => $value) {
                    if ($value == $name) {
                        $data[$name][] = $leaveDates[$key];
                        /** @phpstan-ignore-line */
                    }
                }
            }

            return $data;
        }
    }

}
