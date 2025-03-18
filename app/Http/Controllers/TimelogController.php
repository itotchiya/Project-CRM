<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Project;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use App\Models\ProjectTimeLog;
use App\Exports\EmployeeTimelogs;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectTimeLogBreak;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\TimeLogsDataTable;
use App\Http\Requests\TimeLogs\StartTimer;
use App\Http\Requests\TimeLogs\StopTimer;
use App\Http\Requests\TimeLogs\StoreTimeLog;
use App\Http\Requests\TimeLogs\UpdateTimeLog;
use DateTime;

class TimelogController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.timeLogs';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('timelogs', $this->user->modules));

            return $next($request);
        });
    }

    public function index(TimeLogsDataTable $dataTable)
    {
        $viewPermission = $this->viewTimelogPermission;
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->employees = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
            $this->projects = Project::allProjects();
        }

        $this->timelogMenuType = 'index';

        return $dataTable->render('timelogs.index', $this->data);

    }

    public function calculateTime(Request $request)
    {
        // Get the start and end date/time values
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;

        $dateFormat = company()->date_format;
        $timeFormat = company()->time_format;

        $startDateTimeString = $startDate . ' ' . $startTime;
        $endDateTimeString = $endDate . ' ' . $endTime;

        $startDateTime = Carbon::createFromFormat($dateFormat . ' ' . $timeFormat, $startDateTimeString);
        $endDateTime = Carbon::createFromFormat($dateFormat . ' ' . $timeFormat, $endDateTimeString);

        // Check if both DateTime objects are valid
        if (!$startDateTime || !$endDateTime) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid date or time format.'
            ]);
        }

        // Calculate the difference in minutes
        $diffInMinutes = $endDateTime->getTimestamp() - $startDateTime->getTimestamp();
        $diffInMinutes = $diffInMinutes / 60;

        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        // If time difference is negative, return a warning
        if ($hours < 0 || $minutes < 0) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.totalTimeZero')
            ]);
        }

        // Return the hours and minutes back to the frontend
        return response()->json([
            'status' => 'success',
            'hours' => $hours,
            'minutes' => $minutes
        ]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_timelogs') !== 'all');
        ProjectTimeLog::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeStatus($request)
    {
        abort_403(user()->permission('edit_timelogs') !== 'all');
        ProjectTimeLog::whereIn('id', explode(',', $request->row_ids))->update(
            [
                'approved' => $request->status,
                'approved_by' => (($request->status == 1) ? user()->id : null)
            ]
        );
    }

    public function create()
    {
        $this->pageTitle = __('modules.timeLogs.logTime');
        $this->addTimelogPermission = user()->permission('add_timelogs');
        session(['add_timelogs_permission' => $this->addTimelogPermission]);
        abort_403(!in_array($this->addTimelogPermission, ['all', 'added']));

        if (request()->has('default_assign') && request('default_assign') != '') {
            $assignId = request('default_assign');
            $this->projects = $projects = Project::whereHas('members', function ($query) use ($assignId) {
                $query->where('user_id', $assignId);
            })
                ->orWhere('projects.public', 1)
                ->orderBy('project_name', 'asc')->get();
        }
        elseif (request()->has('default_project') && request('default_project') != '') {
            $defaultProject = request('default_project');
            $this->projects = $projects = Project::where('id', $defaultProject)
                ->get();
        }
        else {
            $this->projects = Project::allProjects();
        }

        $this->tasks = Task::timelogTasks(request('default_project'));

        $timeLog = new ProjectTimeLog();

        $getCustomFieldGroupsWithFields = $timeLog->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'timelogs.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('timelogs.create', $this->data);

    }

    public function store(StoreTimeLog $request)
    {
        $startDateTime = Carbon::createFromFormat($this->company->date_format, $request->start_date, $this->company->timezone)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->company->time_format, $request->start_time)->format('H:i:s');
        $startDateTime = Carbon::parse($startDateTime, $this->company->timezone)->setTimezone('UTC');

        $endDateTime = Carbon::createFromFormat($this->company->date_format, $request->end_date, $this->company->timezone)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->company->time_format, $request->end_time)->format('H:i:s');
        $endDateTime = Carbon::parse($endDateTime, $this->company->timezone)->setTimezone('UTC');

        $timeLog = new ProjectTimeLog();

        if ($request->has('project_id')) {
            $timeLog->project_id = $request->project_id;
        }

        $timeLog->task_id = $request->task_id;
        $timeLog->user_id = $request->user_id;
        $userID = $request->user_id;

        $activeTimer = ProjectTimeLog::with('user')
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(
                    function ($q1) use ($startDateTime, $endDateTime) {
                        $q1->where('end_time', '>', $startDateTime->format('Y-m-d H:i:s'));
                        $q1->where('end_time', '<', $endDateTime->format('Y-m-d H:i:s'));
                    }
                )
                    ->orWhere(
                        function ($q1) use ($startDateTime) {
                            $q1->whereDate('start_time', $startDateTime->format('Y-m-d'));
                            $q1->whereNull('end_time');
                        }
                    );
            })
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->where('user_id', $userID)
            ->first();

        if (is_null($activeTimer)) {
            $timeLog->start_time = $startDateTime;
            $timeLog->end_time = $endDateTime;
            $timeLog->total_hours = $endDateTime->diffInHours($startDateTime);
            $timeLog->total_minutes = $endDateTime->diffInMinutes($startDateTime);

//            $timeLog->total_hours = $timeLog->end_time->diffInHours($timeLog->start_time);
//            $timeLog->total_minutes = $timeLog->end_time->diffInMinutes($timeLog->start_time);
            $timeLog->hourly_rate = 0;
            $timeLog->memo = $request->memo;
            $timeLog->edited_by_user = user()->id;
            $timeLog->save();

            if ($request->custom_fields_data) {
                $timeLog->updateCustomFieldData($request->custom_fields_data);
            }

            return Reply::successWithData(__('messages.timeLogAdded'), ['redirectUrl' => route('timelogs.index')]);
        }

        return Reply::error(__('messages.timelogAlreadyExist'));
    }

    public function destroy($id)
    {
        $timelog = ProjectTimeLog::with('project')->findOrFail($id);
        $deleteTimelogPermission = user()->permission('delete_timelogs');
        abort_403(!($deleteTimelogPermission == 'all'
            || ($deleteTimelogPermission == 'added' && $timelog->added_by == user()->id)
            || ($timelog->project && ($timelog->project->project_admin == user()->id))
        ));

        ProjectTimeLog::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function edit($id)
    {
        $this->pageTitle = __('modules.timeLogs.logTime');
        $editTimelogPermission = $this->editTimelogPermission = user()->permission('edit_timelogs');
        $timeLog = $this->timeLog = ProjectTimeLog::with('user', 'project', 'task')->findOrFail($id)->withCustomFields();
        abort_403(!(
            $editTimelogPermission == 'all'
            || ($editTimelogPermission == 'added' && $timeLog->added_by == user()->id)
            || ($this->timeLog->project && ($this->timeLog->project->project_admin == user()->id))
            || ($editTimelogPermission == 'owned'
                && (($timeLog->project && $timeLog->project->client_id == user()->id) || $timeLog->user_id == user()->id)
            )
            || ($editTimelogPermission == 'both' && (($timeLog->project && $timeLog->project->client_id == user()->id) || $timeLog->user_id == user()->id || $timeLog->added_by == user()->id))
        ));

        if (!is_null($this->timeLog->task_id) && !is_null($this->timeLog->project_id)) {
            $this->tasks = Task::timelogTasks($this->timeLog->project_id);
            $this->employees = $this->timeLog->task->users;
        }
        else if (!is_null($this->timeLog->project_id)) {
            $this->tasks = Task::timelogTasks($this->timeLog->project_id);
            $this->employees = $this->timeLog->project->projectMembers;
        }
        else {
            $this->tasks = Task::timelogTasks();
            $this->employees = $this->timeLog->task->users;
        }

        $this->projects = Project::allProjects();

        $getCustomFieldGroupsWithFields = $this->timeLog->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }


        $this->view = 'timelogs.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('timelogs.create', $this->data);

    }

    public function update(UpdateTimeLog $request, $id)
    {
        $timeLog = ProjectTimeLog::findOrFail($id);

        $startDateTime = Carbon::createFromFormat($this->company->date_format, $request->start_date, $this->company->timezone)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->company->time_format, $request->start_time)->format('H:i:s');
        $startDateTime = Carbon::parse($startDateTime, $this->company->timezone)->setTimezone('UTC');

        $endDateTime = Carbon::createFromFormat($this->company->date_format, $request->end_date, $this->company->timezone)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->company->time_format, $request->end_time)->format('H:i:s');
        $endDateTime = Carbon::parse($endDateTime, $this->company->timezone)->setTimezone('UTC');


        if ($request->has('project_id')) {
            $timeLog->project_id = $request->project_id;
        }

        $timeLog->task_id = $request->task_id;

        if ($request->has('user_id')) {
            $userID = $request->user_id;
        }
        else {
            $userID = $timeLog->user_id;
        }

        $activeTimer = ProjectTimeLog::with('user')
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(
                    function ($q1) use ($startDateTime, $endDateTime) {
                        $q1->where('end_time', '>', $startDateTime->format('Y-m-d H:i:s'));
                        $q1->where('end_time', '<', $endDateTime->format('Y-m-d H:i:s'));
                    }
                )
                    ->orWhere(
                        function ($q1) use ($startDateTime) {
                            $q1->whereDate('start_time', $startDateTime->format('Y-m-d'));
                            $q1->whereNull('end_time');
                        }
                    );
            })
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->where('user_id', $userID)
            ->where('project_time_logs.id', '!=', $id)
            ->select('project_time_logs.*')
            ->first();

        if (is_null($activeTimer)) {
            $timeLog->start_time = $startDateTime->format('Y-m-d H:i:s');
            $timeLog->end_time = $endDateTime->format('Y-m-d H:i:s');
            $timeLog->total_hours = $endDateTime->diffInHours($timeLog->start_time);
            $timeLog->total_minutes = $endDateTime->diffInMinutes($timeLog->start_time);

            $timeLog->memo = $request->memo;
            $timeLog->user_id = $userID;
            $timeLog->edited_by_user = user()->id;
            $timeLog->save();

            // To add custom fields data
            if ($request->custom_fields_data) {
                $timeLog->updateCustomFieldData($request->custom_fields_data);
            }

            return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('timelogs.index')]);
        }

        return Reply::error(__('messages.timelogAlreadyExist'));
    }

    public function show($id)
    {
        $this->pageTitle = __('app.menu.timeLogs');
        $this->editTimelogPermission = user()->permission('edit_timelogs');
        $this->timeLog = ProjectTimeLog::with('user', 'user.employeeDetail', 'project', 'task', 'breaks', 'activeBreak')->findOrFail($id)->withCustomFields();

        abort_403(!(
            $this->viewTimelogPermission == 'all'
            || ($this->viewTimelogPermission == 'added' && $this->timeLog->added_by == user()->id)
            || ($this->viewTimelogPermission == 'owned'
                && (($this->timeLog->project && $this->timeLog->project->client_id == user()->id) || $this->timeLog->user_id == user()->id)
            )
            || ($this->viewTimelogPermission == 'both' && (($this->timeLog->project && $this->timeLog->project->client_id == user()->id) || $this->timeLog->user_id == user()->id || $this->timeLog->added_by == user()->id))
        ));

        $getCustomFieldGroupsWithFields = $this->timeLog->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'timelogs.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('timelogs.create', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function showTimer()
    {
        $this->addTimelogPermission = user()->permission('add_timelogs');
        abort_403(!in_array($this->addTimelogPermission, ['all', 'added', 'owned', 'both']));

        $activeTimer = ProjectTimeLog::with('user')
            ->whereNull('end_time')
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->where('user_id', $this->user->id)->first();

        if (is_null($activeTimer)) {
            $this->tasks = Task::join('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->with('project')
                ->pending()
                ->where('task_users.user_id', '=', $this->user->id)
                ->select('tasks.*')
                ->get();

            $this->projects = Project::byEmployee(user()->id);

            return view('timelogs.ajax.timer', $this->data);

        }
        else {
            return $this->showActiveTimer();
        }
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function startTimer(StartTimer $request)
    {
        $timeLog = new ProjectTimeLog();
        $activeTimer = ProjectTimeLog::selfActiveTimer();

        if (is_null($activeTimer) || (!is_null($activeTimer->activeBreak))) {
            $taskId = $request->task_id;

            if ($request->has('create_task')) {
                $task = new Task();
                $task->heading = $request->memo;
                $task->board_column_id = $this->company->default_task_status;
                $task->is_private = $request->has('is_private') && $request->is_private == 'true' ? 1 : 0;
                $task->start_date = now($this->company->timezone)->format('Y-m-d');
                $task->due_date = now($this->company->timezone)->format('Y-m-d');

                if ($request->project_id != '') {
                    $task->project_id = $request->project_id;
                    $project = request('project_id') ? Project::findOrFail(request('project_id')) : null;
                }

                $task->save();

                if (isset($project)) {
                    $projectLastTaskCount = Task::projectTaskCount($project->id);
                    $task->task_short_code = ($project) ? $project->project_short_code . '-' . ((int)$projectLastTaskCount + 1) : null;
                }

                $task->saveQuietly();

                $taskId = $task->id;
            }

            if ($request->project_id != '') {
                $timeLog->project_id = $request->project_id;
            }

            $timeLog->task_id = $taskId;

            $timeLog->user_id = $this->user->id;
            $timeLog->start_time = now();
            $timeLog->hourly_rate = 0;
            $timeLog->memo = $request->memo;
            $timeLog->save();

            if ($request->project_id != '') {
                $this->logProjectActivity($request->project_id, 'modules.tasks.timerStartedBy');
                $this->logUserActivity($this->user->id, 'modules.tasks.timerStartedProject');
            }
            else {
                $this->logUserActivity($this->user->id, 'modules.tasks.timerStartedTask');
            }

            $this->logTaskActivity($timeLog->task_id, user()->id, 'timerStartedBy');

            /** @phpstan-ignore-next-line */
            $html = $this->showActiveTimer()->render();

            $this->activeTimerCount = ProjectTimeLog::whereNull('end_time')
                ->join('users', 'users.id', '=', 'project_time_logs.user_id')
                ->select('project_time_logs.id');

            if ($this->viewTimelogPermission != 'all' && manage_active_timelogs() != 'all') {
                $this->activeTimerCount->where('project_time_logs.user_id', $this->user->id);
            }

            $this->activeTimerCount = $this->activeTimerCount->count();

            $this->selfActiveTimer = ProjectTimeLog::selfActiveTimer();
            $clockHtml = view('sections.timer_clock', $this->data)->render();

            return Reply::successWithData(__('messages.timerStartedSuccessfully'), ['html' => $html, 'activeTimerCount' => $this->activeTimerCount, 'clockHtml' => $clockHtml]);
        }

        return Reply::error(__('messages.timerAlreadyRunning'));
    }

    public function stopTimer(StopTimer $request)
    {
        $timeId = $request->timeId;
        $timeLog = ProjectTimeLog::with('activeBreak', 'project')->findOrFail($timeId);

        $taskUrl = route('tasks.show', $timeLog->task_id);
        $dashboardUrl = route('dashboard');

        $reload = ($request->currentUrl == $taskUrl || $request->currentUrl == $dashboardUrl) ? 'yes' : 'no';

        abort_403(!(in_array('admin', user_roles()) ||
            (($timeLog->project && $timeLog->project->client_id == user()->id)
                || $timeLog->user_id == user()->id || $timeLog->added_by == user()->id)
            || ($timeLog->project && ($timeLog->project->project_admin == user()->id))
        ));

        $timeLog->end_time = now();
        $timeLog->save();

        $timeLog->total_hours = $timeLog->end_time->diffInHours($timeLog->start_time);
        $timeLog->total_minutes = $timeLog->end_time->diffInMinutes($timeLog->start_time);
        $timeLog->edited_by_user = $this->user->id;
        $timeLog->memo = $request->memo;
        $timeLog->save();

        // Stop breaktime if active
        /** @phpstan-ignore-next-line */
        if (!is_null($timeLog->activeBreak)) {
            /** @phpstan-ignore-next-line */
            $activeBreak = $timeLog->activeBreak;
            $activeBreak->end_time = $timeLog->end_time;
            $activeBreak->total_minutes = $timeLog->end_time->diffInMinutes($activeBreak->start_time);
            $activeBreak->total_hours = $timeLog->end_time->diffInHours($activeBreak->start_time);
            $activeBreak->save();
        }

        if (!is_null($timeLog->project_id)) {
            $this->logProjectActivity($timeLog->project_id, 'modules.tasks.timerStoppedBy');
        }

        if (!is_null($timeLog->task_id)) {
            $this->logTaskActivity($timeLog->task_id, user()->id, 'timerStoppedBy');
        }

        $this->logUserActivity($this->user->id, 'modules.tasks.timerStoppedBy');

        /** @phpstan-ignore-next-line */
        $html = $this->showActiveTimer()->render();

        $this->activeTimerCount = ProjectTimeLog::whereNull('end_time')
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->select('project_time_logs.id');

        if ($this->viewTimelogPermission != 'all' && manage_active_timelogs() != 'all') {
            $this->activeTimerCount->where('project_time_logs.user_id', $this->user->id);
        }

        $this->activeTimerCount = $this->activeTimerCount->count();

        $selfActiveTimer = ProjectTimeLog::doesnthave('activeBreak')
            ->where('user_id', user()->id)
            ->whereNull('end_time')
            ->first();

        return Reply::successWithData(__('messages.timerStoppedSuccessfully'), ['html' => $html, 'activeTimerCount' => $this->activeTimerCount, 'activeTimer' => $selfActiveTimer, 'reload' => $reload]);
    }

    public function showActiveTimer()
    {
        $activeTimelogPermission = user()->permission('manage_active_timelogs');
        $viewTimelogPermission = user()->permission('view_timelogs');

        abort_403($viewTimelogPermission == 'none' || in_array('client', user_roles()));

        $with = ['task', 'task.project', 'user', 'project']; // Define common with clauses

        $this->myActiveTimer = ProjectTimeLog::with($with)
            ->where('user_id', user()->id)
            ->whereNull('end_time')
            ->first();

        $query = ProjectTimeLog::with($with)
            ->whereNull('end_time');

        if ($activeTimelogPermission != 'all') {
            $query->when($viewTimelogPermission == 'owned', function ($q) {
                $q->where('user_id', user()->id);
            })->when($viewTimelogPermission == 'added', function ($q) {
                $q->where('added_by', user()->id);
            })->when($viewTimelogPermission == 'both', function ($q) {
                $q->where(function ($q) {
                    $q->where('user_id', '=', user()->id);
                    $q->orWhere('added_by', '=', user()->id);
                });
            });
        }

        $this->activeTimers = $query->get();

        $this->tasks = Task::select('tasks.*')
            ->with('project')
            ->pending()
            ->join('task_users', 'task_users.task_id', '=', 'tasks.id')
            ->where('task_users.user_id', '=', $this->user->id)
            ->get();

        $this->projects = Project::byEmployee(user()->id);

        $this->selfActiveTimer = $this->myActiveTimer;

        return view('timelogs.ajax.active_timer', $this->data);
    }

    public function byEmployee()
    {
        $viewPermission = $this->viewTimelogPermission;
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->employees = User::allEmployees(null, true);
        $this->projects = Project::allProjects();
        $this->timeLogProjects = $this->projects;
        $this->tasks = Task::all();
        $this->timeLogTasks = $this->tasks;

        $this->activeTimers = $this->activeTimerCount;
        $this->startDate = now()->startOfMonth()->format(company()->date_format);

        $this->endDate = now()->format(company()->date_format);

        $this->timelogMenuType = 'byEmployee';

        return view('timelogs.by_employee', $this->data);
    }

    public function employeeData(Request $request)
    {
        $employee = $request->employee;
        $projectId = $request->projectID;
        $this->viewTimelogPermission = user()->permission('view_timelogs');

        $query = User::with('employeeDetail.designation:id,name')
            ->join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id');

        $where = [];

        if ($projectId != 'all') {
            $where[] = ['project_time_logs.project_id', '=', $projectId];
        }

        $query->leftJoin('project_time_logs', function ($join) use ($where) {
            $join->on('project_time_logs.user_id', '=', 'users.id')
                ->leftJoin('tasks', 'project_time_logs.task_id', '=', 'tasks.id') // Join tasks table
                ->whereNull('tasks.deleted_at') // Exclude soft-deleted tasks
                ->when($where, function ($join, $conditions) {
                    foreach ($conditions as $condition) {
                        $join->where($condition[0], $condition[1], $condition[2] ?? null);
                    }
                });
        });

        $query->select([
            'users.id',
            'users.name',
            'users.image',
            'designations.name as designation_name',
            DB::raw('SUM(project_time_logs.total_minutes) as total_minutes'),
            DB::raw('SUM(project_time_log_breaks.total_minutes) as total_break_minutes'),
            DB::raw('SUM(project_time_logs.earnings) as earnings'),
        ])
            ->leftJoin('project_time_log_breaks', function ($join) use ($where) {
                $join->on('project_time_log_breaks.project_time_log_id', '=', 'project_time_logs.id')
                    ->when($where, function ($join, $conditions) {
                        foreach ($conditions as $condition) {
                            $join->where($condition[0], $condition[1], $condition[2] ?? null);
                        }
                    });
            });

        if (!is_null($employee) && $employee !== 'all') {
            $query->where('project_time_logs.user_id', $employee);
        }

        if ($this->viewTimelogPermission == 'owned') {
            $query->where('project_time_logs.user_id', user()->id);
        }
        else if ($this->viewTimelogPermission == 'added') {
            $query->where('project_time_logs.added_by', user()->id);
        }
        else if ($this->viewTimelogPermission == 'both') {
            $query->where(function ($q) {
                $q->where('project_time_logs.added_by', user()->id)
                    ->orWhere('project_time_logs.user_id', '=', user()->id);
            });
        }

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);

            if (!is_null($startDate)) {
                $query->where(DB::raw('DATE(project_time_logs.`start_time`)'), '>=', $startDate);
            }
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);

            if (!is_null($endDate)) {
                $query->where(function ($query) use ($endDate) {
                    $query->where(DB::raw('DATE(project_time_logs.`end_time`)'), '<=', $endDate);
                });
            }
        }

        $this->employees = $query->groupBy('project_time_logs.user_id')
            ->orderBy('users.name')
            ->get();


        $html = view('timelogs.ajax.member-list', $this->data)->render();

        return Reply::dataOnly(['html' => $html]);
    }

    public function userTimelogs(Request $request)
    {
        $employee = $request->employee;
        $projectId = $request->projectID;

        $query = ProjectTimeLog::with('project', 'task')->select('*')->whereHas('task', function ($query) {
            $query->whereNull('deleted_at');
        })->where('user_id', $employee);

        if (!empty($request->startDate) && !empty($request->endDate)) {

            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->startOfDay()->toDateTimeString();
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->endOfDay()->toDateTimeString();

            // Apply date filter
            $query->whereBetween('start_time', [$startDate, $endDate])
                  ->whereNotNull('end_time');
        }

        if ($projectId != 'all') {
            $query->where('project_id', $projectId);
        }

        $this->timelogs = $query->orderByDesc('end_time')->get();

        $html = view('timelogs.ajax.user-timelogs', $this->data)->render();

        return Reply::dataOnly(['html' => $html]);
    }

    public function approveTimelog(Request $request)
    {
        ProjectTimeLog::where('id', $request->id)->update(
            [
                'approved' => 1,
                'approved_by' => user()->id
            ]
        );

        return Reply::dataOnly(['status' => 'success']);
    }

    public function export()
    {
        abort_403(!canDataTableExport());

        return Excel::download(new EmployeeTimelogs, 'timelogs.xlsx');
    }

    public function pauseTimer(Request $request)
    {
        $timeId = $request->timeId;
        $timeLog = ProjectTimeLog::findOrFail($timeId);

        $taskUrl = route('tasks.show', $timeLog->task_id);
        $dashboardUrl = route('dashboard');

        $reload = ($request->currentUrl == $taskUrl || $request->currentUrl == $dashboardUrl) ? 'yes' : 'no';

        abort_403(!(
            (($timeLog->project && $timeLog->project->client_id == user()->id)
                || $timeLog->user_id == user()->id || $timeLog->added_by == user()->id)
            || ($timeLog->project && ($timeLog->project->project_admin == user()->id))
        ));

        $timeLogBreak = ProjectTimeLogBreak::where('project_time_log_id', $timeLog->id)->whereNull('end_time')->first() ?: new ProjectTimeLogBreak();
        $timeLogBreak->project_time_log_id = $timeLog->id;
        $timeLogBreak->start_time = now();
        $timeLogBreak->total_minutes = 0;
        $timeLogBreak->save();

        if (!is_null($timeLog->project_id)) {
            $this->logProjectActivity($timeLog->project_id, 'modules.tasks.timerPausedBy');
        }

        if (!is_null($timeLog->task_id)) {
            $this->logTaskActivity($timeLog->task_id, user()->id, 'timerPausedBy');
        }

        $this->logUserActivity($this->user->id, 'modules.tasks.timerPausedBy');

        /** @phpstan-ignore-next-line */
        $html = $this->showActiveTimer()->render();

        $this->selfActiveTimer = $timeLog;

        $clockHtml = view('sections.timer_clock', $this->data)->render();

        return Reply::successWithData(__('messages.timerPausedSuccessfully'), ['html' => $html, 'clockHtml' => $clockHtml, 'reload' => $reload]);
    }

    public function resumeTimer(Request $request)
    {
        $timeId = $request->timeId;
        $timeLogBreak = ProjectTimeLogBreak::findOrfail($timeId);
        $timeLog = ProjectTimeLog::findOrFail($timeLogBreak->project_time_log_id);

        $taskUrl = route('tasks.show', $timeLog->task_id);
        $dashboardUrl = route('dashboard');

        $reload = ($request->currentUrl == $taskUrl || $request->currentUrl == $dashboardUrl) ? 'yes' : 'no';

        abort_403(!(
            (($timeLog->project && $timeLog->project->client_id == user()->id)
                || $timeLog->user_id == user()->id || $timeLog->added_by == user()->id)
            || ($timeLog->project && ($timeLog->project->project_admin == user()->id))
        ));

        $activeTimer = ProjectTimeLog::selfActiveTimer();
        $totalActiveTimer = ProjectTimeLog::totalActiveTimer();

        $activeBreak = 0;

        if (count($totalActiveTimer) > 1) {
            foreach ($totalActiveTimer as $key => $break) {
                $activeBreak += $break->activeBreak ? 1 : 0;
            }

            if ($activeBreak != count($totalActiveTimer))
            {
                return Reply::error(__('messages.timerAlreadyRunning'));
            }
        }

        if (is_null($activeTimer) || (!is_null($activeTimer) && !is_null($activeTimer->activeBreak))) {

            $endTime = now();
            $timeLogBreak->end_time = $endTime;

            $timeLogBreak->total_hours = $endTime->diffInHours($timeLogBreak->start_time);

            $timeLogBreak->total_minutes = $endTime->diffInMinutes($timeLogBreak->start_time);
            $timeLogBreak->save();

            if (!is_null($timeLog->task_id)) {
                $this->logTaskActivity($timeLog->task_id, user()->id, 'timerResumedBy');
            }

            $this->logUserActivity($this->user->id, 'modules.tasks.timerStartedBy');

            /** @phpstan-ignore-next-line */
            $html = $this->showActiveTimer()->render();
            $this->selfActiveTimer = ProjectTimeLog::selfActiveTimer();

            $clockHtml = view('sections.timer_clock', $this->data)->render();

            return Reply::successWithData(__('messages.timerStartedSuccessfully'), ['html' => $html, 'clockHtml' => $clockHtml, 'reload' => $reload]);

        }

        return Reply::error(__('messages.timerAlreadyRunning'));

    }

    public function timerData()
    {

        $this->selfActiveTimer = ProjectTimeLog::selfActiveTimer();

        return Reply::dataOnly(['status' => 'success', 'data' => $this->selfActiveTimer]);

    }

    public function stopperAlert($id)
    {
        $timeLogg = ProjectTimeLog::findOrFail($id);

        if (is_null($timeLogg->end_time)) {

            $totalMinutes = (($timeLogg->activeBreak) ? $timeLogg->activeBreak->start_time->diffInMinutes($timeLogg->start_time) : now()->diffInMinutes($timeLogg->start_time)) - $timeLogg->breaks->sum('total_minutes');
        }
        else {
            $totalMinutes = $timeLogg->total_minutes - $timeLogg->breaks->sum('total_minutes');
        }

        $timeLogged = CarbonInterval::formatHuman($totalMinutes);

        /** @phpstan-ignore-line */

        return view('timelogs.stopper-alert', ['timeLogg' => $timeLogged, 'timeLog' => $timeLogg]);
    }

}
