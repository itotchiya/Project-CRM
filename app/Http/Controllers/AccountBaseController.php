<?php

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use Carbon\Carbon;
use App\Models\UserChat;
use App\Models\TaskHistory;
use App\Models\UserActivity;
use App\Models\ProjectTimeLog;
use App\Models\ProjectActivity;
use Illuminate\Support\Facades\App;
use App\Traits\UniversalSearchTrait;
use Illuminate\Support\Facades\Route;

class AccountBaseController extends Controller
{

    use  UniversalSearchTrait;

    /**
     * UserBaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (!(app()->runningInConsole() || config('app.seeding'))) {
            $this->currentRouteName = request()->route()->getName();
        }

        $this->middleware(function ($request, $next) {

            // Keep this function at top
            $this->adminSpecific();

            // Call this function after adminSpecific
            $this->common();

            return $next($request);
        });
    }

    public function adminSpecific()
    {

        abort_403(!user()->admin_approval && request()->ajax());

        if (!user()->admin_approval && Route::currentRouteName() != 'account_unverified') {
            // send() is added to force redirect from here rather return to called function
            return redirect(route('account_unverified'))->send();
        }

        $this->adminTheme = admin_theme();
        $this->invoiceSetting = invoice_setting();

        $this->modules = user_modules();

        if ((in_array('messages', user_modules()))) {
            $this->unreadMessagesCount = UserChat::where('to', user()->id)
                ->where('message_seen', 'no')
                ->count();
        }

        $this->viewTimelogPermission = user()->permission('view_timelogs');

        $activeTimerQuery = ProjectTimeLog::whereNull('end_time')
            ->doesntHave('activeBreak')
            ->join('users', 'users.id', '=', 'project_time_logs.user_id');

        if ($this->viewTimelogPermission != 'all' && manage_active_timelogs() != 'all') {
            $activeTimerQuery->where('project_time_logs.user_id', user()->id);
        }

        $this->activeTimerCount = $activeTimerQuery->count();

        $this->selfActiveTimer = ProjectTimeLog::selfActiveTimer();

        $this->customLink = custom_link_setting();
    }

    public function common()
    {
        $this->fields = [];
        $this->languageSettings = language_setting();
        $this->pushSetting = push_setting();
        $this->smtpSetting = smtp_setting();
        $this->pusherSettings = pusher_settings();

        App::setLocale(user()->locale);
        Carbon::setLocale(user()->locale);
        setlocale(LC_TIME, user()->locale . '_' . mb_strtoupper($this->company->locale));

        $this->user = user();
        $this->unreadNotificationCount = count($this->user?->unreadNotifications);
        $this->stickyNotes = $this->user->sticky;

        $this->worksuitePlugins = worksuite_plugins();

        $this->checkListTotal = GlobalSetting::CHECKLIST_TOTAL;

        if (in_array('admin', user_roles())) {
            $this->appTheme = admin_theme();
            $this->checkListCompleted = GlobalSetting::checkListCompleted();
        }
        else if (in_array('client', user_roles())) {
            $this->appTheme = client_theme();
        }
        else {
            $this->appTheme = employee_theme();
        }

        $this->sidebarUserPermissions = sidebar_user_perms();
    }

    public function logProjectActivity($projectId, $text)
    {
        $activity = new ProjectActivity();
        $activity->project_id = $projectId;
        $activity->activity = $text;
        $activity->save();
    }

    public function logUserActivity($userId, $text)
    {
        $activity = new UserActivity();
        $activity->user_id = $userId;
        $activity->activity = $text;
        $activity->save();
    }

    public function logTaskActivity($taskID, $userID, $text, $boardColumnId = null, $subTaskId = null)
    {
        $activity = new TaskHistory();
        $activity->task_id = $taskID;

        if (!is_null($subTaskId)) {
            $activity->sub_task_id = $subTaskId;
        }

        $activity->user_id = $userID;
        $activity->details = $text;

        if (!is_null($boardColumnId)) {
            $activity->board_column_id = $boardColumnId;
        }

        $activity->save();
    }

}


