<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\WeeklyTimesheet;
use App\Models\WeeklyTimesheetEntries;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Helper\Reply;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectTimeLog;
use App\Events\SubmitWeeklyTimesheet;
use App\Events\WeeklyTimesheetApprovedEvent;
use App\Events\WeeklyTimesheetDraftEvent;

class WeeklyTimesheetController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.weeklyTimesheets';
        $this->middleware(function ($request, $next) {
            $this->timelogMenuType = 'weekly-timesheets';
            abort_403(!in_array('timelogs', $this->user->modules));

            return $next($request);
        });
    }

    public function index()
    {

        $this->timelogMenuType = 'weekly-timesheets';

        if (request()->ajax()) {
            return $this->weekSummaryData(request());
        }

        if (request()->view == 'pending_approval') {
            $this->weeklyTimesheet = WeeklyTimesheet::where('weekly_timesheets.status', 'pending');

            if (user()->reportingTeam->count() > 0 && !in_array('admin', user_roles())) {
                $this->weeklyTimesheet = $this->weeklyTimesheet->join('users', 'weekly_timesheets.user_id', 'users.id')
                ->join('employee_details', 'employee_details.user_id', 'users.id')
                ->whereIn('employee_details.reporting_to', [user()->id]);
            } elseif (user()->reportingTeam->count() == 0 && !in_array('admin', user_roles())) {
                $this->weeklyTimesheet = $this->weeklyTimesheet->where('weekly_timesheets.user_id', user()->id);
            }

            if (request()->id) {
                $this->weeklyTimesheet = $this->weeklyTimesheet->where('weekly_timesheets.id', request()->id);
            }

            $this->weeklyTimesheet = $this->weeklyTimesheet->select('weekly_timesheets.*')->get();

            return view('weekly-timesheets.pending_approval', $this->data);

        }

        $this->pendingApproval = WeeklyTimesheet::where('weekly_timesheets.status', 'pending');

        if (user()->reportingTeam->count() > 0 && !in_array('admin', user_roles())) {
            $this->pendingApproval = $this->pendingApproval->join('users', 'weekly_timesheets.user_id', 'users.id')
            ->join('employee_details', 'employee_details.user_id', 'users.id')
            ->whereIn('employee_details.reporting_to', [user()->id]);
        } elseif (user()->reportingTeam->count() == 0 && !in_array('admin', user_roles())) {
            $this->pendingApproval = $this->pendingApproval->where('weekly_timesheets.user_id', user()->id);
        }

        $this->pendingApproval = $this->pendingApproval->count();

        $now = now();
        $this->year = $now->format('Y');
        $this->month = $now->format('m');

        return view('weekly-timesheets.index', $this->data);
    }

    public function weekSummaryData($request)
    {

        $now = Carbon::parse($request->week_start_date, company()->timezone);
        $this->weekStartDate = $now->copy()->startOfWeek(attendance_setting()->week_start_from);
        $this->weekEndDate = $this->weekStartDate->copy()->addDays(6);
        $this->weekPeriod = CarbonPeriod::create($this->weekStartDate, $this->weekEndDate); // Get All Dates from start to end date

        $this->weekTimesheet = WeeklyTimesheet::where('user_id', user()->id)
        ->whereDate('week_start_date', $this->weekStartDate)
        ->first();

        $weekDates = [];

        foreach($this->weekPeriod as $date) {
            $dateFormatted = $date->format('Y-m-d');
            $weekDates[] = $dateFormatted;
        }

        $tasks = Task::leftJoin('task_users', 'task_users.task_id', '=', 'tasks.id')
            ->where('task_users.user_id', '=', user()->id)
            ->where(function ($query) {
                return $query->where(function ($q) {
                    // Task starts before or on end date AND ends after or on start date
                    $q->whereDate('tasks.start_date', '<=', $this->weekEndDate)
                      ->where(function ($q) {
                        $q->whereDate('tasks.due_date', '>=', $this->weekStartDate)
                          ->orWhereNull('tasks.due_date');
                      });
                });
            })
            ->select('tasks.id', 'tasks.heading')
            ->get();

        $this->tasksForWeek = $tasks;
        $this->weekDates = $weekDates;

        $view = view('weekly-timesheets.ajax.week_summary_data', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view]);
    }

    public function store(Request $request)
    {
        $taskIds = $request->task_ids;
        $dates = $request->dates;
        $hours = $request->hours;

        $this->validate($request, [
            'task_ids' => 'required'
        ], [], [
            'task_ids' => __('app.task')
        ]);

        reset($taskIds);

        $firstKey = key($taskIds);
        
        $weeklyTimesheet = WeeklyTimesheet::firstOrNew(['user_id' => user()->id, 'week_start_date' => $dates[$firstKey][0]]);
        $weeklyTimesheet->status = $request->status;
        $weeklyTimesheet->save();

        $weeklyTimesheet->entries()->delete();
        ProjectTimeLog::where('weekly_timesheet_id', $weeklyTimesheet->id)->delete();

        foreach ($taskIds as $key => $taskId) {

            foreach($dates[$key] as $key2 => $date) {
                $weeklyTimesheetEntry = WeeklyTimesheetEntries::firstOrNew(['weekly_timesheet_id' => $weeklyTimesheet->id, 'date' => $date, 'task_id' => $taskId]);

                if ($weeklyTimesheetEntry->exists) {
                    $hour = $weeklyTimesheetEntry->hours;
                } else {
                    $hour = 0;
                }

                $weeklyTimesheetEntry->hours = $hour + $hours[$key][$key2];
                $weeklyTimesheetEntry->save();

                if ($weeklyTimesheet->status == 'pending' && $weeklyTimesheetEntry->hours > 0) {

                    $timeLog = new ProjectTimeLog();
                    $timeLog->task_id = $taskId;
                    $timeLog->user_id = $weeklyTimesheet->user_id;
                    $timeLog->total_hours = $weeklyTimesheetEntry->hours;
                    $timeLog->total_minutes = $weeklyTimesheetEntry->hours * 60;
                    $timeLog->start_time = Carbon::parse($date)->format('Y-m-d H:i:s');
                    $timeLog->end_time = Carbon::parse($date)->addHours($weeklyTimesheetEntry->hours)->format('Y-m-d H:i:s');
                    $timeLog->weekly_timesheet_id = $weeklyTimesheet->id;
                    $timeLog->save();
                }
            }
        }

        if ($weeklyTimesheet->status == 'pending') {
            SubmitWeeklyTimesheet::dispatch($weeklyTimesheet);

            return Reply::redirect(route('weekly-timesheets.index'), __('messages.recordSaved'));
        }

        return Reply::success(__('messages.recordSaved'));
    }

    public function changeStatus(Request $request)
    {
        $this->validate($request, [
            'reason' => 'required_if:status,draft'
        ], [
        'reason.required_if' => ':attribute ' . __('app.required')
        ]);

        $weeklyTimesheet = WeeklyTimesheet::find($request->timesheetId);
        $weeklyTimesheet->status = $request->status;
        $weeklyTimesheet->approved_by = user()->id;
        $weeklyTimesheet->reason = $request->has('reason') ? $request->reason : null;
        $weeklyTimesheet->save();

        if ($request->status == 'approved') {
            ProjectTimeLog::where('weekly_timesheet_id', $weeklyTimesheet->id)->update(['approved' => 1]);

            WeeklyTimesheetApprovedEvent::dispatch($weeklyTimesheet);
        }

        if ($request->status == 'draft') {
            ProjectTimeLog::where('weekly_timesheet_id', $weeklyTimesheet->id)->delete();
                    
            WeeklyTimesheetDraftEvent::dispatch($weeklyTimesheet);
        }

        return Reply::success(__('messages.recordSaved'));
    }

    public function show($id)
    {
        $this->weeklyTimesheet = WeeklyTimesheet::findOrFail($id);

        $this->weekStartDate = $this->weeklyTimesheet->week_start_date;
        $this->weekEndDate = $this->weekStartDate->copy()->addDays(6);
        $this->weekPeriod = CarbonPeriod::create($this->weekStartDate, $this->weekEndDate);

        $weekDates = [];

        foreach($this->weekPeriod as $date) {
            $dateFormatted = $date->format('Y-m-d');
            $weekDates[] = $dateFormatted;
        }

        $this->weekDates = $weekDates;

        if (request()->ajax()) {
            $this->pageTitle = __('app.menu.weeklyTimesheets') . ' ' . __('app.details');
            $view = 'weekly-timesheets.ajax.show';

            return $this->returnAjax($view);
        }

        $this->view = 'weekly-timesheets.ajax.show';

        return view('weekly-timesheets.show', $this->data);
    }

    public function showRejectModal(Request $request)
    {
        $this->weeklyTimesheet = WeeklyTimesheet::findOrFail($request->timesheet_id);
        return view('weekly-timesheets.reject_modal', $this->data);
    }

    public function edit($id)
    {
        $this->weeklyTimesheet = WeeklyTimesheet::findOrFail($id);

        

        $this->weekStartDate = $this->weeklyTimesheet->week_start_date;
        $this->weekEndDate = $this->weekStartDate->copy()->addDays(6);
        $this->weekPeriod = CarbonPeriod::create($this->weekStartDate, $this->weekEndDate); // Get All Dates from start to end date
        
        $this->weekTimesheet = WeeklyTimesheet::where('user_id', user()->id)
        ->whereDate('week_start_date', $this->weekStartDate)
        ->first();

        $weekDates = [];

        foreach($this->weekPeriod as $date) {
            $dateFormatted = $date->format('Y-m-d');
            $weekDates[] = $dateFormatted;
        }

        $tasks = Task::leftJoin('task_users', 'task_users.task_id', '=', 'tasks.id')
        ->where('task_users.user_id', '=', user()->id)
        ->where(function ($query) {
            return $query->whereBetween(DB::raw('DATE(tasks.`start_date`)'), [$this->weekStartDate, $this->weekEndDate])
            ->orWhereBetween(DB::raw('DATE(tasks.`due_date`)'), [$this->weekStartDate, $this->weekEndDate]);
        })
        ->select('tasks.id', 'tasks.heading')->get();

        $this->tasksForWeek = $tasks;
        $this->weekDates = $weekDates;

        $this->view = 'weekly-timesheets.ajax.week_summary_data';

        $now = now();
        $this->year = $now->format('Y');
        $this->month = $now->format('m');
        return view('weekly-timesheets.edit', $this->data);

    }

}
