<?php

namespace App\Http\Controllers;

use App\DataTables\ArchiveProjectsDataTable;
use App\DataTables\ArchiveTasksDataTable;
use App\DataTables\DiscussionDataTable;
use App\DataTables\ExpensesDataTable;
use App\DataTables\InvoicesDataTable;
use App\DataTables\OrdersDataTable;
use App\DataTables\PaymentsDataTable;
use App\DataTables\ProjectNotesDataTable;
use App\DataTables\ProjectsDataTable;
use App\DataTables\TasksDataTable;
use App\DataTables\TicketDataTable;
use App\DataTables\TimeLogsDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Project\StoreProject;
use App\Http\Requests\Project\UpdateProject;
use App\Imports\ProjectImport;
use App\Jobs\ImportProjectJob;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\DiscussionCategory;
use App\Models\Expense;
use App\Models\GanttLink;
use App\Models\Invoice;
use App\Models\MessageSetting;
use App\Models\Payment;
use App\Models\Pinned;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectCategory;
use App\Models\ProjectDepartment;
use App\Models\ProjectFile;
use App\Models\ProjectMember;
use App\Models\ProjectMilestone;
use App\Models\ProjectNote;
use App\Models\ProjectStatusSetting;
use App\Models\ProjectTemplate;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use App\Models\SubTask;
use App\Models\SubTaskFile;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\TaskboardColumn;
use App\Models\Team;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Traits\ImportExcel;
use App\Traits\ProjectProgress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonInterval;
use Symfony\Component\Mailer\Exception\TransportException;

class ProjectController extends AccountBaseController
{

    use ProjectProgress, ImportExcel;

    private $onlyTrashedRecords = true;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projects';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('projects', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProjectsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_projects');
        abort_403((!in_array($viewPermission, ['all', 'added', 'owned', 'both'])));

        if (!request()->ajax()) {

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
                $this->allEmployees = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
            }

            $this->categories = ProjectCategory::all();
            $this->departments = Team::all();
            $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();
        }

        return $dataTable->render('projects.index', $this->data);

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
        case 'archive':
            $this->archiveRecords($request);

            return Reply::success(__('messages.projectArchiveSuccessfully'));
        case 'change-status':
            $this->changeStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_projects') != 'all');

        Project::withTrashed()->whereIn('id', explode(',', $request->row_ids))->forceDelete();

        $items = explode(',', $request->row_ids);

        foreach ($items as $item) {
            // Delete project files
            Files::deleteDirectory(ProjectFile::FILE_PATH . '/' . $item);
        }
    }

    protected function archiveRecords($request)
    {
        abort_403(user()->permission('edit_projects') != 'all');

        Project::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    public function archiveDestroy($id)
    {
        Project::destroy($id);

        return Reply::success(__('messages.projectArchiveSuccessfully'));
    }

    protected function changeStatus($request)
    {


        abort_403(user()->permission('edit_projects') != 'all');

        Project::whereIn('id', explode(',', $request->row_ids))->update(['status' => $request->status]);
    }

    public function updateStatus(Request $request, $id)
    {

        Project::findOrFail($id)
            ->update([
                'status' => $request->status,
            ]);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->deletePermission = user()->permission('delete_projects');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $project->added_by == user()->id)));

        // Delete project files
        Files::deleteDirectory(ProjectFile::FILE_PATH . '/' . $id);
        $project->forceDelete();

        return Reply::success(__('messages.deleteSuccess'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_projects');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->pageTitle = __('app.addProject');
        $this->clients = User::allClients(null, false, ($this->addPermission == 'all' ? 'all' : null));
        $this->categories = ProjectCategory::all();
        $this->templates = ProjectTemplate::all();
        $this->currencies = Currency::all();
        $this->teams = Team::all();
        $this->employees = User::allEmployees(null, true, ($this->addPermission == 'all' ? 'all' : null));
        $this->redirectUrl = request()->redirectUrl;

        $this->project = (request()['duplicate_project']) ? Project::with('client', 'members', 'members.user', 'members.user.session', 'members.user.employeeDetail.designation', 'milestones', 'milestones.currency')->withTrashed()->findOrFail(request()['duplicate_project'])->withCustomFields() : null;

        if ($this->project) {
            $this->projectMembers = $this->project->members ? $this->project->members->pluck('user_id')->toArray() : null;
        }

        $this->projectTemplate = request('template') ? ProjectTemplate::with('projectMembers')->findOrFail(request('template')) : null;

        if ($this->projectTemplate) {
            $templateMembers = ProjectTemplate::findOrFail(request('template'));
            $this->projectTemplateMembers = $templateMembers->members ? $templateMembers->members->pluck('user_id')->toArray() : null;
            // do not remove below commented line...
            // $this->projectTemplateMembers = $this->projectTemplate->projectMembers ? $this->projectTemplate->projectMembers->pluck('id')->toArray() : null;
        }

        $project = new Project();

        $getCustomFieldGroupsWithFields = $project->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        if (in_array('client', user_roles())) {
            $this->client = User::withoutGlobalScope(ActiveScope::class)->findOrFail(user()->id);

        }
        else {
            $this->client = isset(request()->default_client) ? User::withoutGlobalScope(ActiveScope::class)->findOrFail(request()->default_client) : null;
        }

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        $this->view = 'projects.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('projects.create', $this->data);

    }

    /**
     * @param StoreProject $request
     * @return array|mixed
     * @throws \Throwable
     */
    public function store(StoreProject $request)
    {

        $this->addPermission = user()->permission('add_projects');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        DB::beginTransaction();

        try {

            $startDate = companyToYmd($request->start_date);
            $deadline = !$request->has('without_deadline') ? companyToYmd($request->deadline) : null;

            $project = new Project();
            $project->project_name = $request->project_name;
            $project->project_short_code = $request->project_code;
            $project->start_date = $startDate;
            $project->deadline = $deadline;
            $project->client_id = $request->client_id;
            $project->public_gantt_chart = $request->public_gantt_chart ?? 'enable';
            $project->public_taskboard = $request->public_taskboard ?? 'enable';
            $project->need_approval_by_admin = $request->need_approval_by_admin ?? '0';

            if (!is_null($request->duplicateProjectID)) {

                $duplicateProject = Project::findOrFail($request->duplicateProjectID);

                $project->project_summary = trim_editor($duplicateProject->project_summary);
                $project->category_id = $duplicateProject->category_id;

                $project->client_view_task = $duplicateProject->client_view_task;
                $project->allow_client_notification = $duplicateProject->allow_client_notification;
                $project->manual_timelog = $duplicateProject->manual_timelog;
                $project->team_id = $duplicateProject->team_id;
                $project->status = 'not started';
                $project->project_budget = $duplicateProject->project_budget;
                $project->currency_id = $duplicateProject->currency_id;
                $project->hours_allocated = $duplicateProject->hours_allocated;
                $project->notes = trim_editor($duplicateProject->notes);

            } else {
                $project->project_summary = trim_editor($request->project_summary);

                if ($request->category_id != '') {
                    $project->category_id = $request->category_id;
                }

                $project->client_view_task = $request->client_view_task ? 'enable' : 'disable';
                $project->allow_client_notification = $request->client_task_notification ? 'enable' : 'disable';
                $project->manual_timelog = $request->manual_timelog ? 'enable' : 'disable';


                $project->project_budget = $request->project_budget;
                $project->currency_id = $request->currency_id != '' ? $request->currency_id : company()->currency_id;
                $project->hours_allocated = $request->hours_allocated;

                $defaultsStatus = ProjectStatusSetting::where('default_status', 1)->get();

                foreach ($defaultsStatus as $default) {
                    $project->status = $default->status_name;
                }

                $project->miro_board_id = $request->miro_board_id;
                $project->client_access = $request->has('client_access') && $request->client_access ? 1 : 0;
                $project->enable_miroboard = $request->has('miroboard_checkbox') && $request->miroboard_checkbox ? 1 : 0;
                $project->notes = trim_editor($request->notes);

            }

            if ($request->public) {
                $project->public = $request->public ? 1 : 0;
            }

            $project->save();

            if ($request->has('team_id') && is_array($request->team_id) && count($request->team_id) > 0) {
                foreach ($request->team_id as $team) {
                    ProjectDepartment::create([
                        'project_id' => $project->id,
                        'team_id' => $team
                    ]);
                }
            }

            if (trim_editor($request->notes) != '') {
                $project->notes()->create([
                    'title' => 'Note',
                    'details' => $request->notes,
                    'client_id' => $request->client_id,
                ]);
            }

            $this->logSearchEntry($project->id, $project->project_name, 'projects.show', 'project');
            $this->logProjectActivity($project->id, 'messages.addedAsNewProject');

            if ($request->template_id) {
                $template = ProjectTemplate::with('projectMembers')->findOrFail($request->template_id);
                $counter = 1;
                foreach ($template->tasks as $task) {

                    $projectTask = new Task();
                    $projectTask->project_id = $project->id;
                    $projectTask->heading = $task->heading;
                    $projectTask->task_category_id = $task->project_template_task_category_id;
                    $projectTask->description = trim_editor($task->description);
                    $projectTask->start_date = $startDate;
                    $projectTask->due_date = $deadline;
                    $projectTask->priority = $task->priority;

                    if(isset($project->project_short_code)){
                        $projectTask->task_short_code = $project->project_short_code . '-' . $counter;
                    }else{
                        $projectTask->task_short_code = $counter;
                    }
                    $projectTask->is_private = 0;
                    $projectTask->save();

                    $taskLabels = explode(",", $task->task_labels);

                    if (!empty($taskLabels) && count($taskLabels) > 0 && !in_array('', $taskLabels)) {

                        $projectTask->labels()->sync($taskLabels);
                    }

                    foreach ($task->usersMany as $value) {
                        TaskUser::create(
                            [
                                'user_id' => $value->id,
                                'task_id' => $projectTask->id
                            ]
                        );
                    }

                    foreach ($task->subtasks as $value) {
                        $projectTask->subtasks()->create(['title' => $value->title]);
                    }

                    $counter++;
                }
            }

            if (!is_null($request->duplicateProjectID)) {
                $this->storeDuplicateProject($request, $project);
            }

            // To add custom fields data
            if ($request->custom_fields_data) {
                $project->updateCustomFieldData($request->custom_fields_data);
            }

            // Commit Transaction
            DB::commit();

            if($request->has('type') && $request->type == 'duplicateProject'){
                return Reply::success(__('messages.projectCopiedSuccessfully'));
            }
            else {

                $redirectUrl = urldecode($request->redirect_url);

                if ($redirectUrl == '') {
                    $redirectUrl = route('projects.index');
                }

                return Reply::dataOnly(['projectID' => $project->id, 'redirectUrl' => $redirectUrl]);
            }

        } catch (TransportException $e) {
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Please configure SMTP details to add project. Visit Settings -> notification setting to set smtp ' . $e->getMessage(), 'smtp_error');
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->project = Project::with('client', 'members', 'members.user', 'members.user.session', 'members.user.employeeDetail.designation', 'milestones', 'milestones.currency', 'departments')
            ->withTrashed()
            ->findOrFail($id)
            ->withCustomFields();

        $memberIds = $this->project->members->pluck('user_id')->toArray();

        $this->editPermission = user()->permission('edit_projects');
        $this->editProjectMembersPermission = user()->permission('edit_project_members');

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && user()->id == $this->project->added_by)
            || ($this->editPermission == 'owned' && user()->id == $this->project->client_id && in_array('client', user_roles()))
            || ($this->editPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($this->editPermission == 'both' && (user()->id == $this->project->client_id || user()->id == $this->project->added_by))
            || ($this->editPermission == 'both' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
        ));

        $this->pageTitle = __('app.update') . ' ' . __('app.project');

        $getCustomFieldGroupsWithFields = $this->project->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->clients = User::allClients(null, false, ($this->editPermission == 'all' ? 'all' : null));
        $this->categories = ProjectCategory::all();
        $this->currencies = Currency::all();
        $this->teams = Team::all();
        $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();
        $this->departmentIds = $this->project->departments->pluck('team_id')->toArray();


        /**
         * If the project has departments, it retrieves the team IDs associated with those departments and fetches the users belonging to each team.
         * If the project does not have any departments, its giving all the employees.
         */
        if ($this->project->departments->count() > 0 && ($this->editPermission == 'all' || $this->editProjectMembersPermission == 'all')) {
            $this->teamIds = $this->project->departments->pluck('team_id')->toArray();
            $this->employees = collect([]);

            foreach ($this->teamIds as $teamId) {
                $team = User::departmentUsers($teamId);
                $this->employees = $this->employees->merge($team);
            }

        }
        else{
            $this->employees = '';

            if (($this->editPermission == 'all' || $this->editPermission = 'owned') || $this->editProjectMembersPermission == 'all') {
                $this->employees = User::allEmployees(null, false, ($this->editProjectMembersPermission == 'all' ? 'all' : null));
            }
        }

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;

        $this->view = 'projects.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        abort_403(user()->permission('edit_projects') == 'added' && $this->project->added_by != user()->id);

        return view('projects.create', $this->data);

    }

    /**
     * @param UpdateProject $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateProject $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->project_name = $request->project_name;
        $project->project_short_code = $request->project_code;

        $project->project_summary = trim_editor($request->project_summary);

        $project->start_date = companyToYmd($request->start_date);

        if (!$request->has('without_deadline')) {
            $project->deadline = companyToYmd($request->deadline);
        }
        else {
            $project->deadline = null;
        }

        if ($request->notes != '') {
            $project->notes = trim_editor($request->notes);
        }

        if ($request->category_id != '') {
            $project->category_id = $request->category_id;
        }

        if ($request->client_view_task) {
            $project->client_view_task = 'enable';
        }
        else {
            $project->client_view_task = 'disable';
        }

        if ($request->client_task_notification) {
            $project->allow_client_notification = 'enable';
        }
        else {
            $project->allow_client_notification = 'disable';
        }

        if ($request->manual_timelog) {
            $project->manual_timelog = 'enable';
        }
        else {
            $project->manual_timelog = 'disable';
        }

        $project->client_id = ($request->client_id == 'null' || $request->client_id == '') ? null : $request->client_id;

        if ($request->calculate_task_progress) {
            $project->calculate_task_progress = 'true';
            $project->completion_percent = $this->calculateProjectProgress($id, 'true');
            $project->status = $request->status;
        }
        else {
            $project->calculate_task_progress = 'false';
            $project->completion_percent = $request->completion_percent;
            if($request->completion_percent == 100){
                $project->status = 'finished';
            }else{
                $project->status = $request->status;
            }
        }

        $project->project_budget = $request->project_budget;
        $project->currency_id = $request->currency_id != '' ? $request->currency_id : company()->currency_id;
        $project->hours_allocated = $request->hours_allocated;
        $project->miro_board_id = $request->miro_board_id;

        if ($request->has('miroboard_checkbox')) {
            $project->client_access = $request->has('client_access') && $request->client_access ? 1 : 0;
        }
        else {
            $project->client_access = 0;
        }

        $project->enable_miroboard = $request->has('miroboard_checkbox') && $request->miroboard_checkbox ? 1 : 0;

        if ($request->public) {
            $project->public = 1;
        }

        if ($request->private) {
            $project->public = 0;
        }


        if (!$request->private && !$request->public && $request->member_id) {
            $project->projectMembers()->sync($request->member_id);
        }

        if (is_null($request->member_id && $request->has('member_id'))) {
            $project->projectMembers()->detach();
        }

        $project->public_gantt_chart = $request->public_gantt_chart;
        $project->public_taskboard = $request->public_taskboard;
        $project->need_approval_by_admin = $request->need_approval_by_admin;
        $project->save();

        if ($request->has('team_id')) {
            $project->projectDepartments()->sync($request->team_id);
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $project->updateCustomFieldData($request->custom_fields_data);
        }

        $this->logProjectActivity($project->id, 'messages.updateSuccess');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('projects.index');
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['projectID' => $project->id, 'redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $this->viewPermission = user()->permission('view_projects');
        $viewFilePermission = user()->permission('view_project_files');
        $this->viewMiroboardPermission = user()->permission('view_miroboard');
        $viewMilestonePermission = user()->permission('view_project_milestones');
        $this->viewPaymentPermission = user()->permission('view_project_payments');
        $this->viewProjectTimelogPermission = user()->permission('view_project_timelogs');
        $this->viewExpensePermission = user()->permission('view_project_expenses');
        $this->viewRatingPermission = user()->permission('view_project_rating');
        $this->viewBurndownChartPermission = user()->permission('view_project_burndown_chart');
        $this->viewProjectMemberPermission = user()->permission('view_project_members');

        $this->project = Project::with(['client', 'members', 'members.user','mentionProject', 'members.user.session', 'members.user.employeeDetail.designation', 'milestones' => function ($q) use ($viewMilestonePermission) {
            if ($viewMilestonePermission == 'added') {
                $q->where('added_by', user()->id);
            }
        },
            'milestones.currency', 'files' => function ($q) use ($viewFilePermission) {
                if ($viewFilePermission == 'added') {
                    $q->where('added_by', user()->id);
                }
            }])
            ->withTrashed()
            ->findOrFail($id)
            ->withCustomFields();

        $this->projectStatusColor = ProjectStatusSetting::where('status_name', $this->project->status)->first();
        $memberIds = $this->project->members->pluck('user_id')->toArray();
        $mentionIds = $this->project->mentionProject->pluck('user_id')->toArray();

        abort_403(!(
            $this->viewPermission == 'all'
            || $this->project->public
            || ($this->viewPermission == 'added' && user()->id == $this->project->added_by)
            || ($this->viewPermission == 'owned' && user()->id == $this->project->client_id && in_array('client', user_roles()))
            || ($this->viewPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($this->viewPermission == 'both' && (user()->id == $this->project->client_id || user()->id == $this->project->added_by))
            || ($this->viewPermission == 'both' && (in_array(user()->id, $memberIds) || user()->id == $this->project->added_by) && in_array('employee', user_roles()))
           || (($this->viewPermission == 'none') && (!is_null(($this->project->mentionProject))) && in_array(user()->id, $mentionIds))
        ));

        $this->pageTitle = $this->project->project_name;

        $getCustomFieldGroupsWithFields = $this->project->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->messageSetting = MessageSetting::first();
        $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();

        $tab = request('tab');

        switch ($tab) {
        case 'members':
            abort_403(!(
                $this->viewProjectMemberPermission == 'all'
            ));
            $this->view = 'projects.ajax.members';
            break;
        case 'milestones':
            $this->project = Project::with(['milestones' => function($query) {
                $query->withCount('tasks');
            }])->findOrFail($id);
            $this->view = 'projects.ajax.milestones';
            break;
        case 'taskboard':
            session()->forget('pusher_settings');
            $this->view = 'projects.ajax.taskboard';
            break;
        case 'tasks':
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->unAssignedTask = $this->project->tasks()->whereDoesntHave('users')->count();
            return (!$this->project->trashed()) ? $this->tasks($this->project->project_admin == user()->id) : $this->archivedTasks($this->project->project_admin == user()->id);
        case 'gantt':
            $this->hideCompleted = request('hide_completed') ?? 0;
            // $this->taskBoardStatus = TaskboardColumn::all();
            $this->ganttData = $this->ganttDataNew($this->project->id, $this->hideCompleted, $this->project->company);
            $this->view = 'projects.ajax.gantt_dhtml';
            // $this->view = 'projects.ajax.gantt';
            break;
        case 'invoices':
            return $this->invoices();
        case 'files':
            $this->view = 'projects.ajax.files';
            break;
        case 'timelogs':
            $this->employees = User::allEmployees();
            return $this->timelogs($this->project->project_admin == user()->id);
        case 'expenses':
            return $this->expenses();
        case 'miroboard';
            abort_403(!in_array($this->viewMiroboardPermission, ['all']) || !$this->project->enable_miroboard &&
                ((!in_array('client', user_roles()) && !$this->project->client_access && $this->project->client_id != user()->id)));
            $this->view = 'projects.ajax.miroboard';
            break;
        case 'payments':
            return $this->payments();
        case 'discussion':
            $this->discussionCategories = DiscussionCategory::orderBy('order', 'asc')->get();

            return $this->discussions($this->project->project_admin == user()->id);
        case 'notes':
            return $this->notes($this->project->project_admin == user()->id);
        case 'rating':
            return $this->rating($this->project->project_admin == user()->id);
        case 'burndown-chart':
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);

            return $this->burndownChart($this->project);
        case 'activity':
            $this->activities = [];

            if(!in_array('client', user_roles())){
                $this->activities = ProjectActivity::getProjectActivities($id, 10);
            }

            $this->view = 'projects.ajax.activity';
            break;
        case 'tickets':
            return $this->tickets($this->project->project_admin == user()->id);
        case 'orders':
            return $this->orders();
        default:
            $this->taskChart = $this->taskChartData($id);
            $hoursLogged = $this->project->times()->sum('total_minutes');

            $breakMinutes = ProjectTimeLogBreak::projectBreakMinutes($id);

            $this->hoursBudgetChart = $this->hoursBudgetChartData($this->project, $hoursLogged, $breakMinutes);

            $this->amountBudgetChart = $this->amountBudgetChartData($this->project);
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->earnings = Payment::where('status', 'complete')
                ->where('project_id', $id)
                ->sum('amount');

            // Initialize variables to store hours and minutes
            $this->hoursLogged = $this->convertMinutesToHoursAndMinutes($hoursLogged, $breakMinutes);

            $this->expenses = Expense::where(['project_id' => $id, 'status' => 'approved'])->sum('price');
            $this->profit = $this->earnings - $this->expenses;

            $this->view = 'projects.ajax.overview';
            break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'overview';

        return view('projects.show', $this->data);

    }

    // Convert minutes in hours and minutes
    public function convertMinutesToHoursAndMinutes($totalMinutes, $breakMinutes = 0)
    {
        $totalMinutes = ($totalMinutes - $breakMinutes);

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return $hours.__('app.hour').' '. $minutes.__('app.minute');
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function taskChartData($id)
    {
        $taskStatus = TaskboardColumn::all();
        $data['labels'] = $taskStatus->pluck('column_name');
        $data['colors'] = $taskStatus->pluck('label_color');
        $data['values'] = [];

        foreach ($taskStatus as $label) {
            $data['values'][] = Task::where('project_id', $id)->where('tasks.board_column_id', $label->id)->count();
        }

        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function hoursBudgetChartData($project, $hoursLogged, $breakMinutes)
    {
        $hoursBudget = $project->hours_allocated ? $project->hours_allocated : 0;

        $hoursLogged = intdiv($hoursLogged - $breakMinutes, 60);
        $overRun = $hoursLogged - $hoursBudget;
        $overRun = $overRun < 0 ? 0 : $overRun;
        $hoursLogged = ($hoursLogged > $hoursBudget) ? $hoursBudget : $hoursLogged;

        $data['labels'] = [__('app.planned'), __('app.actual')];
        $data['colors'] = ['#2cb100', '#d30000'];
        $data['threshold'] = $hoursBudget;
        $dataset = [
            [
                'name' => __('app.planned'),
                'values' => [$hoursBudget, $hoursLogged],
            ],
            [
                'name' => __('app.overrun'),
                'values' => [0, $overRun],
            ],
        ];
        $data['datasets'] = $dataset;
        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function amountBudgetChartData($project)
    {
        $amountBudget = $project->project_budget ?: 0;
        $earnings = Payment::where('status', 'complete')
            ->where('project_id', $project->id)
            ->sum('amount');
        $plannedOverun = $earnings < $amountBudget ? $earnings : $amountBudget;
        $overRun = $earnings - $amountBudget;
        $overRun = $overRun < 0 ? 0 : $overRun;

        $data['labels'] = [__('app.planned'), __('app.actual')];
        $data['colors'] = ['#2cb100', '#d30000'];
        $data['threshold'] = $amountBudget;
        $dataset = [
            [
                'name' => __('app.planned'),
                'values' => [$amountBudget, $plannedOverun],
            ],
            [
                'name' => __('app.overrun'),
                'values' => [0, $overRun],
            ],
        ];
        $data['datasets'] = $dataset;

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function storePin(Request $request)
    {
        $pinned = new Pinned();
        $pinned->task_id = $request->task_id;
        $pinned->project_id = $request->project_id;
        $pinned->save();

        return Reply::success(__('messages.pinnedSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return array
     */
    public function destroyPin(Request $request, $id)
    {
        Pinned::where('project_id', $id)->where('user_id', user()->id)->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function assignProjectAdmin(Request $request)
    {
        $userId = $request->userId;
        $projectId = $request->projectId;
        $project = Project::findOrFail($projectId);
        $project->project_admin = $userId;
        $project->save();

        return Reply::success(__('messages.roleAssigned'));
    }

    public function tasks($projectAdmin = false)
    {
        $dataTable = new TasksDataTable(true);

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

            $viewPermission = user()->permission('view_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }
        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.tasks';

        return $dataTable->render('projects.show', $this->data);

    }

    public function archivedTasks($projectAdmin = false)
    {
        $dataTable = new ArchiveTasksDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

            $viewPermission = user()->permission('view_tasks');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.tasks';

        return $dataTable->render('projects.show', $this->data);

    }

    public function ganttData()
    {
        $id = request('projectID');
        $assignedTo = request('assignedTo');
        $projectTask = request('projectTask');
        $taskStatus = request('taskStatus');
        $milestones = request('milestones');
        $withoutDueDate = false;

        if ($assignedTo != 'all') {
            $tasks = Task::projectTasks($id, $assignedTo, null, $withoutDueDate);
        }
        else {
            $tasks = Task::projectTasks($id, null, null, $withoutDueDate);
        }

        if ($projectTask) {
            $tasks = $tasks->whereIn('id', explode(',', $projectTask));
        }

        if ($taskStatus) {
            $tasks = $tasks->whereIn('board_column_id', explode(',', $taskStatus));
        }

        if ($milestones != '') {
            $tasks = $tasks->whereIn('milestone_id', explode(',', $milestones));
        }

        $data = array();

        $count = 0;

        foreach ($tasks as $task) {
            $data[$count] = [
                'id' => 'task-' . $task->id,
                'name' => $task->heading,
                'start' => ((!is_null($task->start_date)) ? $task->start_date->format('Y-m-d') : ((!is_null($task->due_date)) ? $task->due_date->format('Y-m-d') : null)),
                'end' => (!is_null($task->due_date)) ? $task->due_date->format('Y-m-d') : $task->start_date->format('Y-m-d'),
                'progress' => 0,
                'bg_color' => $task->boardColumn->label_color,
                'taskid' => $task->id,
                'draggable' => true
            ];

            if (!is_null($task->dependent_task_id)) {
                $data[$count]['dependencies'] = 'task-' . $task->dependent_task_id;
            }

            $count++;
        }

        return response()->json($data);
    }

    public function invoices()
    {
        $dataTable = new InvoicesDataTable($this->onlyTrashedRecords);
        $viewPermission = user()->permission('view_project_invoices');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.invoices';

        return $dataTable->render('projects.show', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function invoiceList(Request $request, $id)
    {
        $options = '<option value="">--</option>';

        $viewPermission = user()->permission('view_invoices');

        if (($viewPermission == 'all' || $viewPermission == 'added')) {

            if ($id != 0) {
                $invoices = Invoice::with('payment', 'currency')->where('project_id', $id)->where('send_status', 1)->pending()->get();
            }
            else {
                $invoices = Invoice::with('payment')->where('send_status', 1)
                    ->where(function ($q) {
                        $q->where('status', 'unpaid')
                            ->orWhere('status', 'partial');
                    })->get();
            }

            foreach ($invoices as $item) {
                $paidAmount = $item->amountPaid();

                $options .= '<option data-currency-code="'.$item->currency->currency_code.'" data-currency-id="' . $item->currency_id . '" data-content="' . $item->invoice_number . ' - ' . __('app.total') . ': <span class=\'text-dark f-w-500 mr-2\'>' . currency_format($item->total, $item->currency->id) . ' </span>' . __('modules.invoices.due') . ': <span class=\'text-red\'>' . currency_format(max(($item->total - $paidAmount), 0), $item->currency->id) . '</span>" value="' . $item->id . '"> ' . $item->invoice_number . ' </option>';
            }

        }

        $bankData = '<option value="">--</option>';

        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankDetails = BankAccount::where('status', 1)->where('currency_id', $request->currencyId);

        if($this->viewBankAccountPermission == 'added'){
            $bankDetails = $bankDetails->where('added_by', user()->id);
        }

        $bankDetails = $bankDetails->get();

        foreach ($bankDetails as $bankDetail) {

            $bankName = '';

            if($bankDetail->type == 'bank')
            {
                $bankName = $bankDetail->bank_name.' |';
            }

            $bankData .= '<option value="' . $bankDetail->id . '">'.$bankName .' '.$bankDetail->account_name. '</option>';
        }

        $exchangeRate = Currency::where('id', $request->currencyId)->pluck('exchange_rate')->toArray();

        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'account' => $bankData, 'exchangeRate' => $exchangeRate]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function members($id)
    {
        $options = '';
        $userData = [];

        $project = Project::select('id', 'public')->find($id);
        $checkPublic = ($project) ? $project->public : 0;

        if ($id == 0 || $checkPublic == 1) {
            $members = User::allEmployees(null, true);

            foreach ($members as $item) {
                $self_select = (user() && user()->id == $item->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';

                $options .= '<option  data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div> ' . $item->name . '' . $self_select . '</span>" value="' . $item->id . '"> ' . $item->name . '</option>';
            }

            $projectShortCode = '--';
        }
        else {

            $members = ProjectMember::with('user')->where('project_id', $id)->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })->get();


            foreach ($members as $item) {
                $content = ( $item->user->status == 'deactive') ? "<span class='badge badge-pill badge-danger border align-center ml-2 px-2'>Inactive</span>" : '';
                $self_select = (user() && user()->id == $item->user->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';

                $options .= '<option
                data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->user->image_url . ' ></div> ' . $item->user->name . '' . $self_select . '' . $content .'</span>"
                value="' . $item->user->id . '"> ' . $item->user->name . ' </option>';

                $url = route('employees.show', [$item->user->id]);

                $userData[] = ['id' => $item->user->id, 'value' => $item->user->name, 'image' => $item->user->image_url, 'link' => $url];
            }


            $project = Project::findOrFail($id);
            $projectShortCode = $project->project_short_code;

        }

        return Reply::dataOnly(['status' => 'success', 'unique_id' => $projectShortCode, 'data' => $options, 'userData' => $userData]);

    }

    public function timelogs($projectAdmin = false)
    {
        $dataTable = new TimeLogsDataTable($this->onlyTrashedRecords);

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_timelogs');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.timelogs';

        return $dataTable->render('projects.show', $this->data);
    }

    public function expenses()
    {
        $dataTable = new ExpensesDataTable($this->onlyTrashedRecords);
        $viewPermission = user()->permission('view_project_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.expenses';

        return $dataTable->render('projects.show', $this->data);

    }

    public function payments()
    {
        $dataTable = new PaymentsDataTable($this->onlyTrashedRecords);
        $viewPermission = user()->permission('view_project_payments');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.payments';

        return $dataTable->render('projects.show', $this->data);

    }

    public function discussions($projectAdmin = false)
    {
        $dataTable = new DiscussionDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_discussions');
            abort_403(!in_array($viewPermission, ['all', 'added']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.discussion';

        return $dataTable->render('projects.show', $this->data);

    }

    public function burndown(Request $request, $id)
    {
        $this->project = Project::with(['tasks' => function ($query) use ($request) {
            if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
                $query->where(DB::raw('DATE(`start_date`)'), '>=', Carbon::createFromFormat($this->company->date_format, $request->startDate));
            }

            if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
                $query->where(DB::raw('DATE(`due_date`)'), '<=', Carbon::createFromFormat($this->company->date_format, $request->endDate));
            }

            $query->whereNotNull('due_date');
        }])->withTrashed()->findOrFail($id);

        $this->totalTask = $this->project->tasks->count();
        $datesArray = [];
        $startDate = $request->startDate ? Carbon::createFromFormat($this->company->date_format, $request->startDate) : Carbon::parse($this->project->start_date);

        if ($this->project->deadline) {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->company->date_format, $request->endDate) : Carbon::parse($this->project->deadline);
        }
        else {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->company->date_format, $request->endDate) : now();
        }

        for ($startDate; $startDate <= $endDate; $startDate->addDay()) {
            $datesArray[] = $startDate->format($this->company->date_format);
        }

        $uncompletedTasks = [];
        $createdTasks = [];
        $deadlineTasks = [];
        $deadlineTasksCount = [];
        $this->datesArray = json_encode($datesArray);

        foreach ($datesArray as $key => $value) {

            if (Carbon::createFromFormat($this->company->date_format, $value)->lessThanOrEqualTo(now())) {
                $uncompletedTasks[$key] = $this->project->tasks->filter(function ($task) use ($value) {

                    if (is_null($task->completed_on)) {
                        return true;
                    }

                    return $task->completed_on ? $task->completed_on->greaterThanOrEqualTo(Carbon::createFromFormat($this->company->date_format, $value)) : false;
                })->count();

                $createdTasks[$key] = $this->project->tasks->filter(function ($task) use ($value) {
                    return Carbon::createFromFormat($this->company->date_format, $value)->startOfDay()->equalTo($task->created_at->startOfDay());
                })->count();

                if ($key > 0) {
                    $uncompletedTasks[$key] += $createdTasks[$key];
                }

            }

            $deadlineTasksCount[] = $this->project->tasks->filter(function ($task) use ($value) {
                return Carbon::createFromFormat($this->company->date_format, $value)->startOfDay()->equalTo($task->due_date->startOfDay());
            })->count();

            if ($key == 0) {
                $deadlineTasks[$key] = $this->totalTask - $deadlineTasksCount[$key];
            }
            else {
                $newKey = $key - 1;
                $deadlineTasks[$key] = $deadlineTasks[$newKey] - $deadlineTasksCount[$key];
            }
        }

        $this->uncompletedTasks = json_encode($uncompletedTasks);
        $this->deadlineTasks = json_encode($deadlineTasks);

        if ($request->ajax()) {
            return $this->data;
        }

        $this->startDate = $request->startDate ? Carbon::parse($request->startDate)->format($this->company->date_format) : Carbon::parse($this->project->start_date)->format($this->company->date_format);
        $this->endDate = $endDate->format($this->company->date_format);

        return view('projects.ajax.burndown', $this->data);
    }

    public function notes($projectAdmin = false)
    {
        $dataTable = new ProjectNotesDataTable();

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_note');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';

        $this->view = 'projects.ajax.notes';

        return $dataTable->render('projects.show', $this->data);

    }

    public function tickets($projectAdmin = false)
    {
        $dataTable = new TicketDataTable($this->onlyTrashedRecords);

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_tickets');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $this->activeTab = request()->tab ?: 'profile';
        $this->view = 'projects.ajax.tickets';

        return $dataTable->render('projects.show', $this->data);

    }

    public function burndownChart($project)
    {
        $viewPermission = user()->permission('view_project_burndown_chart');
        abort_403(!(in_array($viewPermission, ['all']) || $project->project_admin == user()->id));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'burndown-chart';
        $this->view = 'projects.ajax.burndown';

        return view('projects.show', $this->data);

    }

    public function rating($projectAdmin)
    {

        if (!$projectAdmin) {
            $viewPermission = user()->permission('view_project_rating');
            abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        }

        $this->deleteRatingPermission = user()->permission('delete_project_rating');
        $this->editRatingPermission = user()->permission('edit_project_rating');
        $this->addRatingPermission = user()->permission('add_project_rating');


        $tab = request('tab');
        $this->activeTab = $tab ?: 'rating';


        $this->view = 'projects.ajax.rating';

        return view('projects.show', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function archive(ArchiveProjectsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_projects');
        abort_403($viewPermission == 'none');

        if (!request()->ajax()) {

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
                $this->allEmployees = User::allEmployees(null, true);
            }

            $this->categories = ProjectCategory::all();
            $this->departments = Team::all();
        }

        return $dataTable->render('projects.archive', $this->data);

    }

    public function archiveRestore($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $project->restore();

        return Reply::success(__('messages.projectRevertSuccessfully'));
    }

    public function importProject()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.projects');

        $this->addPermission = user()->permission('add_projects');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'projects.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('projects.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, ProjectImport::class);

        if($rvalue == 'abort'){
            return Reply::error(__('messages.abortAction'));
        }

        $view = view('projects.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, ProjectImport::class, ImportProjectJob::class);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    public function changeProjectStatus(Request $request)
    {
        $projectId = $request->projectId;
        $statusID = $request->statusId;
        $project = Project::with('members')->findOrFail($projectId);
        $projectUsers = $project->members->pluck('user_id')->toArray();

        $this->editProjectPermission = user()->permission('edit_projects');

        abort_403(!(
            $this->editProjectPermission == 'all'
            || ($this->editProjectPermission == 'added' && user()->id == $project->added_by)
            || ($this->editProjectPermission == 'owned' && user()->id == $project->client_id && in_array('client', user_roles()))
            || ($this->editProjectPermission == 'owned' && in_array(user()->id, $projectUsers) && in_array('employee', user_roles()))
            || ($this->editProjectPermission == 'both' && (user()->id == $project->client_id || user()->id == $project->added_by))
            || ($this->editProjectPermission == 'both' && in_array(user()->id, $projectUsers) && in_array('employee', user_roles())
            )));

        $projectStatus = ProjectStatusSetting::where('status_name', $statusID)->first();

        $project->status = $projectStatus->status_name;
        $project->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function pendingTasks($id)
    {
        if ($id != 0) {
            $tasks = Task::join('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->with('project')
                ->pending()
                ->where('task_users.user_id', '=', $this->user->id)
                ->where('tasks.project_id', '=', $id)
                ->select('tasks.*')
                ->get();

        }
        else {
            $tasks = Task::join('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->with('project')
                ->pending()
                ->where('task_users.user_id', '=', $this->user->id)
                ->select('tasks.*')
                ->get();
        }

        $options = '<option value="">--</option>';

        foreach ($tasks as $item) {
            $name = '';

            if (!is_null($item->project_id)) {
                $name .= "<h5 class='f-12 text-darkest-grey'>" . $item->heading . "</h5><div class='text-muted f-11'>" . $item->project->project_name . '</div>';

            }
            else {
                $name .= "<span class='text-dark-grey f-11'>" . $item->heading . '</span>';
            }

            $options .= '<option data-content="' . $name . '" value="' . $item->id . '">' . $item->heading . '</option>';
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);

    }

    public function ajaxLoadProject(Request $request)
    {
        $search = $request->search;

        $response = [];

        if ($search) {
            $lists = Project::allProjects($search);

            foreach ($lists as $list) {
                $response[] = [
                    'id' => $list->id,
                    'text' => $list->project_name,
                ];
            }
        }


        return response()->json($response);
    }

    public function duplicateProject($id)
    {
        $this->projectId = $id;

        $this->project = Project::findOrFail($id);

        $this->taskboardColumns = TaskboardColumn::orderBy('priority', 'asc')->get();

        $addPermission = user()->permission('add_projects');
        $this->memberIds = $this->project->members->pluck('user_id')->toArray();
        $this->teams = Team::all();
        $this->departmentIds = $this->project->departments->pluck('team_id')->toArray();

        if ($this->project->departments->count() > 0) {
            $this->teamIds = $this->project->departments->pluck('team_id')->toArray();
            $this->employees = collect([]);

            foreach ($this->teamIds as $teamId) {
                $team = User::departmentUsers($teamId);
                $this->employees = $this->employees->merge($team);
            }
        }
        else {
            $this->employees = User::allEmployees(null, true, ($addPermission == 'all' ? 'all' : null));
        }

        $this->clients = User::allClients(null, false, ($addPermission == 'all' ? 'all' : null));

        if (in_array('client', user_roles())) {
            $this->client = User::withoutGlobalScope(ActiveScope::class)->findOrFail(user()->id);

        }
        else {
            $this->client = isset(request()->default_client) ? User::withoutGlobalScope(ActiveScope::class)->findOrFail(request()->default_client) : null;
        }

        return view('projects.duplicate-project', $this->data);
    }

    public function storeDuplicateProject($request, $project)
    {
        // For duplicate project
        if($request->has('file')){

            $projectExists = ProjectFile::where('project_id', $request->duplicateProjectID)->get();

            if ($projectExists) {
                foreach ($projectExists as $projectExist) {
                    $file = new ProjectFile();
                    $file->user_id = $projectExist->user_id;
                    $file->project_id = $project->id;

                    $fileName = Files::generateNewFileName($projectExist->filename);

                    Files::copy(ProjectFile::FILE_PATH . '/' . $projectExist->project_id . '/' . $projectExist->hashname, ProjectFile::FILE_PATH . '/' . $project->id . '/' . $fileName);

                    $file->filename = $projectExist->filename;
                    $file->hashname = $fileName;
                    $file->size = $projectExist->size;
                    $file->save();

                    $this->logProjectActivity($project->id, $this->user->id, 'fileActivity', $project->board_column_id); /* @phpstan-ignore-line */
                }
            }

        }

        if($request->has('milestone')){

            $projectMilestoneExists = ProjectMilestone::where('project_id', $request->duplicateProjectID)->get();

            if ($projectMilestoneExists) {

                foreach ($projectMilestoneExists as $projectMilestoneExist) {
                    $milestone = new ProjectMilestone();
                    $milestone->project_id = $project->id;
                    $milestone->milestone_title = $projectMilestoneExist->milestone_title;
                    $milestone->summary = $projectMilestoneExist->summary;
                    $milestone->cost = $projectMilestoneExist->cost;
                    $milestone->currency_id = $projectMilestoneExist->currency_id;
                    $milestone->status = $projectMilestoneExist->status;
                    $milestone->start_date = $projectMilestoneExist->start_date;
                    $milestone->end_date = $projectMilestoneExist->end_date;
                    $milestone->save();

                    $this->logProjectActivity($milestone->project_id, 'messages.milestoneUpdated');
                }
            }

        }

        if($request->has('time_sheet')){

            $projectTimeLogExists = ProjectTimeLog::where('project_id', $request->duplicateProjectID)->get();

            if ($projectTimeLogExists) {

                foreach ($projectTimeLogExists as $projectTimeLogExist) {
                    $projectTimeLog = new ProjectTimeLog();
                    $projectTimeLog->project_id = $project->id;
                    $projectTimeLog->task_id = $projectTimeLogExist->task_id;
                    $projectTimeLog->user_id = $projectTimeLogExist->user_id;
                    $projectTimeLog->start_time = $projectTimeLogExist->start_time;
                    $projectTimeLog->end_time = $projectTimeLogExist->end_time;
                    $projectTimeLog->total_hours = $projectTimeLogExist->total_hours;
                    $projectTimeLog->total_minutes = $projectTimeLogExist->total_minutes;
                    $projectTimeLog->hourly_rate = $projectTimeLogExist->hourly_rate;
                    $projectTimeLog->memo = $projectTimeLogExist->memo;
                    $projectTimeLog->edited_by_user = user()->id;
                    $projectTimeLog->save();
                }
            }

        }

        if($request->has('note')){

            $projectNoteExists = ProjectNote::where('project_id', $request->duplicateProjectID)->get();

            if ($projectNoteExists) {

                foreach ($projectNoteExists as $projectNoteExist) {
                    $projectNote = new ProjectNote();
                    $projectNote->project_id = $project->id;
                    $projectNote->title = $projectNoteExist->title;
                    $projectNote->details = $projectNoteExist->details;
                    $projectNote->type = $projectNoteExist->type;
                    $projectNote->client_id = $projectNoteExist->client_id;
                    $projectNote->is_client_show = $projectNoteExist->is_client_show;
                    $projectNote->ask_password = $projectNoteExist->ask_password;
                    $projectNote->save();
                }
            }

        }

        if($request->has('task')){

            $projectTasks = Task::with('labels', 'taskUsers');

            if($request->task_status){
                $projectTasks->whereIn('board_column_id', $request->task_status);
            }

            $projectTasks = $projectTasks->where('project_id', $request->duplicateProjectID)->get();
            $taskBoard = TaskboardColumn::where('slug', 'incomplete')->first();

            if ($projectTasks) {

                foreach ($projectTasks as $projectTask) {

                    $task = new Task();
                    $task->company_id = company()->id;
                    $task->project_id = $project->id;
                    $task->heading = $projectTask->heading;
                    $task->description = trim_editor($projectTask->description);
                    $task->start_date = $projectTask->start_date;
                    $task->due_date = $projectTask->due_date;
                    $task->task_category_id = $projectTask->category_id; /* @phpstan-ignore-line */
                    $task->priority = $projectTask->priority;
                    $task->board_column_id = $taskBoard->id;
                    $task->dependent_task_id = $projectTask->dependent_task_id;
                    $task->is_private = $projectTask->is_private;
                    $task->billable = $projectTask->billable;
                    $task->estimate_hours = $projectTask->estimate_hours;
                    $task->estimate_minutes = $projectTask->estimate_minutes;
                    $task->milestone_id = $projectTask->milestone_id;
                    $task->repeat = $projectTask->repeat;
                    $task->hash = md5(microtime());

                    if ($projectTask->repeat) {
                        $task->repeat_count = $projectTask->repeat_count;
                        $task->repeat_type = $projectTask->repeat_type;
                        $task->repeat_cycles = $projectTask->repeat_cycles;
                    }

                    if ($project) {
                        $projectLastTaskCount = Task::projectTaskCount($project->id);
                        $task->task_short_code = ($project) ? $project->project_short_code . '-' . ((int)$projectLastTaskCount + 1) : null;
                    }

                    $task->saveQuietly();

                    $this->saveSubTask($projectTask, $task, $request);
                }
            }
        }
    }

    public function saveSubTask($projectTask, $task, $request)
    {
        if($request->has('same_assignee')){
            foreach($projectTask->taskUsers as $taskUsers){
                $taskUser = new TaskUser();
                $taskUser->task_id = $task->id;
                $taskUser->user_id = $taskUsers->user_id;
                $taskUser->save();
            }
        }

        if (!is_null($projectTask->id) && $request->has('sub_task')) {

            $subTasks = SubTask::with(['files'])->where('task_id', $projectTask->id)->get();

            if ($subTasks) {
                foreach ($subTasks as $subTask) {
                    $subTaskData = new SubTask();
                    $subTaskData->title = $subTask->title;
                    $subTaskData->task_id = $task->id;
                    $subTaskData->description = trim_editor($subTask->description);

                    if ($subTask->start_date != '' && $subTask->due_date != '') {
                        $subTaskData->start_date = $subTask->start_date;
                        $subTaskData->due_date = $subTask->due_date;
                    }

                    $subTaskData->assigned_to = $subTask->assigned_to;

                    $subTaskData->save();

                    if ($subTask->files) {
                        foreach ($subTask->files as $fileData) {
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
    }

    public function getProjects(Request $request)
    {
        $projects = Project::query()
            ->when(($request->requesterType == 'client' && $request->clientId), function ($query) use ($request) {
                $query->where('client_id', $request->clientId);
            })
            ->when(($request->requesterType == 'employee' && $request->userId), function ($query) use ($request) {
                $query->whereHas('members', function ($q) use ($request) {
                    $q->where('user_id', $request->userId);
                })
                ->orWhere('public', 1);
            })
            ->get();

        return Reply::dataOnly(['projects' => $projects]);
    }

    public function orders()
    {
        $dataTable = new OrdersDataTable($this->onlyTrashedRecords);
        $viewPermission = user()->permission('view_project_orders');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        $this->view = 'projects.ajax.orders';

        return $dataTable->render('projects.show', $this->data);
    }

    public function ganttDataNew($projectID, $hideCompleted, $company)
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();

        if ($hideCompleted == 0) {
            $milestones = ProjectMilestone::with(['tasks' => function ($q) {
                return $q->whereNotNull('tasks.start_date');
            }, 'tasks.boardColumn'])->where('project_id', $projectID)->get();

        } else {
            $milestones = ProjectMilestone::with(['tasks' => function ($q) use ($taskBoardColumn) {
                return $q->whereNotNull('tasks.start_date')->where('tasks.board_column_id', '<>', $taskBoardColumn->id);
            }, 'tasks.boardColumn'])
            ->where('status', 'incomplete')
            ->where('project_id', $projectID)->get();
        }

        $nonMilestoneTasks = Task::whereNull('milestone_id')->whereNotNull('start_date')->with('boardColumn');

        if ($hideCompleted == 1) {
            $nonMilestoneTasks = $nonMilestoneTasks->where('tasks.board_column_id', '<>', $taskBoardColumn->id);
        }

        $nonMilestoneTasks = $nonMilestoneTasks->where('project_id', $projectID)->get();

        $ganttData = [];
        $ganttData['data'] = [];
        $ganttData['links'] = [];

        foreach ($milestones as $key => $milestone) {
            $parentID = 'project-' . $milestone->id;

            $ganttData['data'][] = [
                'id' => $parentID,
                'text' => $milestone->milestone_title,
                'type' => 'project',
                'start_date' => $milestone->start_date->format('d-m-Y H:i'),
                'duration' => $milestone->start_date->diffInDays($milestone->end_date) + 1,
                'progress' => ($milestone->tasks->count()) ? ($milestone->completionPercent() / 100) : 0,
                'parent' => 0,
                'open' => ($milestone->status == 'incomplete'),
                'color' => '#cccccc',
                'textColor' => '#09203F',
                'linkable' => false,
                'priority' => ($key + 1)
            ];


            foreach ($milestone->tasks as $key2 => $task) {
                $taskUsers = '<div class="d-inline-flex align-items-center ml-1 text-dark w-180" data-task-id="'.$task->id.'">';

                foreach($task->users as $item) {
                    $taskUsers .= '<img data-toggle="tooltip" class="taskEmployeeImg rounded-circle mr-1" data-original-title="'.$item->name.'"
                                                     src="'.$item->image_url.'">';
                }

                $taskUsers .= view('components.status', ['style' => 'color: ' . $task->boardColumn->label_color, 'value' => $task->boardColumn->column_name, 'color' => 'red'])->render() . '</div>';

                $ganttData['data'][] = [
                    'id' => $task->id,
                    'text' => $task->heading,
                    'text_user' => $taskUsers,
                    'type' => 'task',
                    'start_date' => $task->start_date->format('d-m-Y H:i'),
                    'duration' => (($task->due_date) ? $task->start_date->diffInDays($task->due_date) + 1 : 1),
                    'parent' => $parentID,
                    'priority' => ($key2 + 1),
                    'color' => $task->boardColumn->label_color.'20',
                    'textColor' => '#09203F',
                    'view' => view('components.cards.task-card', ['task' => $task, 'draggable' => false, 'company' => $company])->render()
                ];

                if (!is_null($task->dependent_task_id)) {
                    $ganttData['links'][] = [
                        'id' => $task->id,
                        'source' => $task->dependent_task_id,
                        'target' => $task->id,
                        'type' => 0
                    ];
                }
            }

            if ($milestone->tasks->count()) {
                $ganttData['data'][] = [
                    'id' => 'milestone-' . $milestone->id,
                    'text' => $milestone->milestone_title,
                    'type' => 'milestone',
                    'start_date' => (($task->due_date) ? $task->due_date->format('d-m-Y H:i') : $task->start_date->format('d-m-Y H:i')),
                    'duration' => (($task->due_date) ? $task->start_date->diffInDays($task->due_date) + 1 : 1),
                    'parent' => $parentID,
                ];

                $ganttData['links'][] = [
                    'id' => 'milestone-' . $milestone->id,
                    'source' => $task->id,
                    'target' => 'milestone-' . $milestone->id,
                    'type' => 0
                ];
            }
        }

        foreach ($nonMilestoneTasks as $key2 => $task) {
            $taskUsers = '<div class="d-inline-flex align-items-center ml-1 text-dark w-180" data-task-id="'.$task->id.'">';

            foreach($task->users as $item) {
                $taskUsers .= '<img data-toggle="tooltip" class="taskEmployeeImg rounded-circle mr-1" data-original-title="'.$item->name.'"
                                                 src="'.$item->image_url.'">';
            }

            $taskUsers .= view('components.status', ['style' => 'color: ' . $task->boardColumn->label_color, 'value' => $task->boardColumn->column_name, 'color' => 'red'])->render() . '</div>';

            $ganttData['data'][] = [
                'id' => $task->id,
                'text' => $task->heading,
                'text_user' => $taskUsers,
                'type' => 'task',
                'start_date' => $task->start_date->format('d-m-Y H:i'),
                'duration' => (($task->due_date) ? $task->start_date->diffInDays($task->due_date) : 1),
                'priority' => ($key2 + 1),
                'color' => $task->boardColumn->label_color.'20',
                'textColor' => '#09203F',
                'view' => view('components.cards.task-card', ['task' => $task, 'draggable' => false, 'company' => $company])->render()
            ];

            if (!is_null($task->dependent_task_id)) {
                $ganttData['links'][] = [
                    'id' => $task->id,
                    'source' => $task->dependent_task_id,
                    'target' => $task->id,
                    'type' => 0
                ];
            }

        }

        $ganttData['links'] = array_merge($ganttData['links'], GanttLink::where('project_id', $projectID)->select('id', 'type', 'source', 'target', 'type')->get()->toArray());

        return $ganttData;
    }

}
