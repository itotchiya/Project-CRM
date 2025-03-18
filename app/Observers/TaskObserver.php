<?php

namespace App\Observers;

use App\Events\TaskEvent;
use App\Events\TaskUpdated as EventsTaskUpdated;
use App\Http\Controllers\AccountBaseController;
use App\Models\GoogleCalendarModule;
use App\Models\MentionUser;
use App\Models\Notification;
use App\Models\ProjectTimeLog;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Models\TaskUser;
use App\Traits\ProjectProgress;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Services\Google;
use Carbon\Carbon;
use Google\Service\Exception;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventAttendee;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Support\Facades\Config;
use App\Traits\EmployeeActivityTrait;
use App\Helper\Files;
use App\Models\TaskFile;

class TaskObserver
{

    use ProjectProgress;
    use EmployeeActivityTrait;

    public function saving(Task $task)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $task->last_updated_by = user()->id;

            /* Add/Update google calendar event */
            if (!request()->has('repeat') || request()->repeat == 'no' && !is_null($task->due_date)) {
                $task->event_id = $this->googleCalendarEvent($task);
            }
        }
    }

    public function saved(Task $task)
    {
        /* Add/Update google calendar event */
        if (!request()->has('repeat') || request()->repeat == 'no' && !is_null($task->due_date)) {
            $this->googleCalendarEvent($task);
        }
    }

    public function creating(Task $task)
    {
        $task->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $task->created_by = user()->id;
                $task->added_by = user()->id;
            }

            if (request()->has('board_column_id')) {
                $task->board_column_id = request()->board_column_id;
            }
            else if (isset(company()->default_task_status)) {
                $task->board_column_id = company()->default_task_status;
            }
            else {
                $taskBoard = TaskboardColumn::where('slug', 'incomplete')->first();
                $task->board_column_id = $taskBoard->id;
            }
        }

        if (company()) {
            $task->company_id = company()->id;
        }
    }

    public function created(Task $task)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (user()) {
                self::createEmployeeActivity(user()->id, 'task-created', $task->id, 'task');
            }

            $mentionIds = [];
            $mentionDescriptionMembers = null;
            $unmentionIds = null;
            $unmentionDescriptionMember = null;

            if (request()->mention_user_ids != null || request()->mention_user_ids != '' || request()->has('mention_user_ids')) {

                $task->mentionUser()->sync(request()->mention_user_ids);
                $mentionIds = explode(',', request()->mention_user_ids);
                $mentionDescriptionMembers = User::whereIn('id', $mentionIds)->get();

            }

            if (request()->user_id != null || request()->user_id != '' || request()->has('user_id')) {

                $unmentionIds = array_diff(request()->user_id, $mentionIds);
                $unmentionDescriptionMember = User::whereIn('id', $unmentionIds)->get();
            }

            if (request()->has('project_id') && request()->project_id != 'all' && request()->project_id != '') {
                if ((request()->mention_user_id) != null || request()->mention_user_id != '' || $mentionIds != null && $mentionIds != '') {

                    event(new TaskEvent($task, $mentionDescriptionMembers, 'TaskMention'));

                    if (request()->user_id != null || request()->user_id != '' || request()->has('user_id')) {

                        if ($unmentionIds != null && $unmentionIds != '') {

                            event(new TaskEvent($task, $unmentionDescriptionMember, 'NewTask'));

                        }
                    }

                }
                else {

                    if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable' && $task->project->client->status != 'deactive') {
                        event(new TaskEvent($task, $task->project->client, 'NewClientTask'));
                    }

                }

            }
            else {

                if ((request()->mention_user_id) != null || request()->mention_user_id != '') {

                    event(new TaskEvent($task, $mentionDescriptionMembers, 'TaskMention'));

                }

            }

            $log = new AccountBaseController();

            if (\user()) {
                $log->logTaskActivity($task->id, user()->id, 'createActivity', $task->board_column_id);
            }

            if ($task->project_id) {

                // Calculate project progress if enabled
                $log->logProjectActivity($task->project_id, 'messages.newTaskAddedToTheProject');
                $this->calculateProjectProgress($task->project_id);
            }

            // Log search
            $log->logSearchEntry($task->id, $task->heading, 'tasks.edit', 'task');

            // Sync task users
            if (!empty(request()->user_id) && request()->template_id == '') {

                $task->users()->sync(request()->user_id);

            }

        }
    }

    public function updating(Task $task)
    {

        $mentionedUser = MentionUser::where('task_id', $task->id)->pluck('user_id');
        $requestMentionIds = explode(',', request()->mention_user_ids);
        $newMention = [];
        $task->mentionUser()->sync(request()->mention_user_ids);

        if ($requestMentionIds != null) {
            foreach ($requestMentionIds as $value) {

                if (($mentionedUser) != null) {

                    if (!in_array($value, json_decode($mentionedUser))) {

                        $newMention[] = $value;
                    }
                }
                else {

                    $newMention[] = $value;
                }
            }

            $newMentionMembers = User::whereIn('id', $newMention)->get();

            if (!empty($newMention)) {

                event(new TaskEvent($task, $newMentionMembers, 'TaskMention'));

            }
        }

    }

    // phpcs:ignore
    public function updated(Task $task)
    {
        $movingTaskId = request()->has('movingTaskId'); // If task moved in taskboard


        if (!isRunningInConsoleOrSeeding()) {

            if (user()) {
                self::createEmployeeActivity(user()->id, 'task-updated', $task->id, 'task');
            }

            if ($task->isDirty('board_column_id')) {

                if ($task->boardColumn->slug == 'completed'){
                    $notification = 'TaskCompleted';
                } elseif ($task->boardColumn->slug == 'waiting_approval'){
                    $notification = 'TaskApproval';
                } else {
                    $notification = 'TaskStatusUpdated';
                }

                if($task->boardColumn->slug == 'waiting_approval'){

                    $admins = User::allAdmins($task->company->id);

                    $users = $task->taskUsers;

                    $projectAdmintask = Task::with('project')
                        ->whereHas('project', function ($query) {
                            $query->whereNotNull('project_admin');
                        })
                        ->get()
                        ->pluck('project.projectAdmin');

                    $admins = $users->merge($admins);

                    if (!$projectAdmintask->isEmpty()){
                        $admins = $admins->merge($projectAdmintask);
                    }

                }else{
                    $admins = User::allAdmins($task->company->id);
                }

                // send task complete notification
                event(new TaskEvent($task, $admins, $notification));

                if ($task->addedByUser) {
                    $addedByUserRole = $task->addedByUser->roles->pluck('name')->toArray();

                    if (!is_null($task->added_by) && !in_array('client', $addedByUserRole) && !in_array($task->added_by, $admins->pluck('id')->toArray())) {
                        event(new TaskEvent($task, $task->addedByUser, $notification));
                    }
                }

                $taskUser = $task->users->whereNotIn('id', $admins->pluck('id'))->whereNotIn('id', [$task->added_by]);
                event(new TaskEvent($task, $taskUser, $notification));

                $timeLogs = ProjectTimeLog::with('user')->whereNull('end_time')
                    ->where('task_id', $task->id)
                    ->get();

                if ($timeLogs && ($task->boardColumn->slug == 'completed' || $task->boardColumn->slug == 'waiting_approval')) {
                    foreach ($timeLogs as $timeLog) {

                        $timeLog->end_time = now();
                        $timeLog->edited_by_user = (user()) ? user()->id : null;
                        $timeLog->save();

                        /** @phpstan-ignore-next-line */
                        $timeLog->total_hours = ($timeLog->end_time->diff($timeLog->start_time)->format('%d') * 24) + ($timeLog->end_time->diff($timeLog->start_time)->format('%H'));

                        if ($timeLog->total_hours == 0) {
                            /** @phpstan-ignore-next-line */
                            $timeLog->total_hours = round(($timeLog->end_time->diff($timeLog->start_time)->format('%i') / 60), 2);
                        }

                        /** @phpstan-ignore-next-line */
                        $timeLog->total_minutes = ($timeLog->total_hours * 60) + ($timeLog->end_time->diff($timeLog->start_time)->format('%i'));

                        $timeLog->save();

                        if (!is_null($timeLog->activeBreak)) {
                            /** @phpstan-ignore-next-line */
                            $activeBreak = $timeLog->activeBreak;
                            $activeBreak->end_time = $timeLog->end_time;
                            $activeBreak->save();
                        }
                    }
                }

                if ((request()->project_id && request()->project_id != 'all') || (!is_null($task->project_id))) {
                    $project = $task->project;

                    if ($project->client_id != null && $project->allow_client_notification == 'enable' && $project->client->status != 'deactive') {
                        event(new TaskEvent($task, $project->client, 'TaskCompletedClient'));
                    }
                }

            }

            if (request('user_id')) {
                if (($movingTaskId != '' && $task->id == $movingTaskId) || $movingTaskId == '') {
                    // Send notification to user
                    event(new TaskEvent($task, $task->users, 'TaskUpdated'));
                }
            }
        }

        /* Add/Update google calendar event */
        if (!request()->has('repeat') || request()->repeat == 'no' && !is_null($task->due_date)) {
            $task->event_id = $this->googleCalendarEvent($task);
        }

        if (pusher_settings()->status == 1 && pusher_settings()->taskboard == 1) {
            Config::set('queue.default', 'sync'); // Set intentionally for instant delivery of messages
            Config::set('broadcasting.default', 'pusher'); // Set intentionally for instant delivery of messages
        }

        // Call for Pusher
        event(new EventsTaskUpdated());

        if (\user()) {
            if (($movingTaskId != '' && $task->id == $movingTaskId) || $movingTaskId == '') {
                $log = new AccountBaseController();
                $log->logTaskActivity($task->id, user()->id, 'statusActivity', $task->board_column_id);
            }
        }

        if ($task->project_id) {

            if (($movingTaskId != '' && $task->id == $movingTaskId) || $movingTaskId == '') {
                // Calculate project progress if enabled
                $this->calculateProjectProgress($task->project_id);
            }
        }
    }

    public function deleting(Task $task)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $task->id)->where('module_type', 'task')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifyData = [
            'App\Notifications\NewTask',
            'App\Notifications\TaskUpdated',
            'App\Notifications\TaskComment',
            'App\Notifications\TaskCommentClient',
            'App\Notifications\TaskCompleted',
            'App\Notifications\TaskStatusUpdated',
            'App\Notifications\TaskApproval',
            'App\Notifications\NewClientTask',
            'App\Notifications\TaskCompletedClient',
            'App\Notifications\TaskNote',
            'App\Notifications\TaskNoteClient',
            'App\Notifications\TaskReminder',
            'App\Notifications\TaskUpdatedClient',
            'App\Notifications\SubTaskCreated',
            'App\Notifications\SubTaskCompleted'
        ];

        Notification::whereIn('type', $notifyData)
            ->whereNull('read_at')
            ->where(
                function ($q) use ($task) {
                    $q->where('data', 'like', '{"id":' . $task->id . ',%');
                    $q->orWhere('data', 'like', '%,"task_id":' . $task->id . ',%');
                }
            )->delete();

        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($task->event_id) {
                    $google->service('Calendar')->events->delete('primary', $task->event_id);
                }
            } catch (Exception $error) {
                if (is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }
        }

        $task->files()->each(function ($file) {
            Files::deleteFile($file->hashname, TaskFile::FILE_PATH);
            Files::deleteDirectory(TaskFile::FILE_PATH . '/' . $file->task_id);
            $file->delete();
        });

        /* End of deleting event from google calendar */
    }

    /**
     * @param Task $task
     */
    public function deleted(Task $task)
    {

        if (user()) {
            self::createEmployeeActivity(user()->id, 'task-deleted');

        }

        if (!is_null($task->project_id)) {
            // Calculate project progress if enabled
            $this->calculateProjectProgress($task->project_id);
        }
    }

    protected function googleCalendarEvent($event)
    {
        $module = GoogleCalendarModule::first();
        $googleAccount = company();

        if (!company()) {
            return $event->event_id;
        }

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token && $module->task_status == 1) {

            $google = new Google();
            $attendiesData = [];

            $attendees = TaskUser::with(['user'])->whereHas(
                'user', function ($query) {
                    $query->where('status', 'active')->where('google_calendar_status', true);
                }
            )->where('task_id', $event->id)->get();

            foreach ($attendees as $attend) {
                if (!is_null($attend->user) && !is_null($attend->user->email)) {
                    $attendiesData[] = ['email' => $attend->user->email];
                }
            }

            if ($event->start_date && $event->due_date) {
                $start_date = Carbon::parse($event->start_date)->shiftTimezone($googleAccount->timezone);
                $due_date = Carbon::parse($event->due_date)->shiftTimezone($googleAccount->timezone);

                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new Google_Service_Calendar_Event(
                    array(
                        'summary' => $event->heading,
                        'location' => $googleAccount->address,
                        'description' => $event->description,
                        'colorId' => 7,
                        'start' => array(
                            'dateTime' => $start_date,
                            'timeZone' => $googleAccount->timezone,
                        ),
                        'end' => array(
                            'dateTime' => $due_date,
                            'timeZone' => $googleAccount->timezone,
                        ),
                        'attendees' => $attendiesData,
                        'reminders' => array(
                            'useDefault' => false,
                            'overrides' => array(
                                array('method' => 'email', 'minutes' => 24 * 60),
                                array('method' => 'popup', 'minutes' => 10),
                            ),
                        ),
                    )
                );

                try {
                    if ($event->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                    }
                    else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                } catch (Exception $error) {
                    if (is_null($error->getErrors())) {
                        // Delete google calendar connection data i.e. token, name, google_id
                        $googleAccount->name = null;
                        $googleAccount->token = null;
                        $googleAccount->google_id = null;
                        $googleAccount->google_calendar_verification_status = 'non_verified';
                        $googleAccount->save();
                    }
                }
            }
        }

        return $event->event_id;
    }

    // Google calendar for multiple events
    protected function googleCalendarEventMulti($eventIds)
    {
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token) {
            $google = new Google();
            $events = Task::whereIn('id', $eventIds)->get();
            $event = $events->first();

            $frq = ['day' => 'DAILY', 'week' => 'WEEKLY', 'month', 'MONTHLY', 'year' => 'YEARLY'];
            $frequency = $frq[$event->repeat_type];

            $eventData = new Google_Service_Calendar_Event();
            $eventData->setSummary($event->heading);
            $eventData->setLocation('');

            $start = new Google_Service_Calendar_EventDateTime();
            $start->setDateTime($event->start_date->toAtomString());
            $start->setTimeZone($googleAccount->timezone);

            $eventData->setStart($start);
            $end = new Google_Service_Calendar_EventDateTime();
            $end->setDateTime($event->due_date->toAtomString());
            $end->setTimeZone($googleAccount->timezone);

            $eventData->setEnd($end);
            /** @phpstan-ignore-next-line */
            $eventData->setRecurrence(array('RRULE:FREQ=' . $frequency . ';INTERVAL=' . $event->repeat_every . ';COUNT=' . $event->repeat_cycles . ';'));

            $attendees = TaskUser::with(['user'])->whereHas(
                'user', function ($query) {
                    $query->where('status', 'active')->where('google_calendar_status', true);
                }
            )->where('task_id', $event->id)->get();

            $attendiesData = [];

            foreach ($attendees as $attend) {
                if (!is_null($attend->user) && !is_null($attend->user->email)) {
                    $attendee1 = new Google_Service_Calendar_EventAttendee();
                    $attendee1->setEmail($attend->user->email);
                    $attendiesData[] = $attendee1;
                }
            }

            /** @phpstan-ignore-next-line */
            $eventData->attendees = $attendiesData;

            // Create event
            $google->connectUsing($googleAccount->token);

            try {
                if ($event->event_id) {
                    $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                }
                else {
                    $results = $google->service('Calendar')->events->insert('primary', $eventData);
                }

                foreach ($events as $event) {
                    $event->event_id = $results->id;
                    $event->save();
                }

                return;
            } catch (Exception $error) {
                if (is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }

            foreach ($events as $event) {
                $event->event_id = $event->event_id;
                $event->save();
            }

        }
    }

}
