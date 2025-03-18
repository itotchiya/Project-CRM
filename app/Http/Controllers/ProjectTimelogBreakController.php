<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\TimelogBreak\UpdateTimelogBreak;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use Carbon\Carbon;

class ProjectTimelogBreakController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('timelogs', $this->user->modules));
            return $next($request);
        });
    }

    public function edit($id)
    {
        $this->timelogBreak = ProjectTimeLogBreak::findOrFail($id);
        return view('timelog-break.edit', $this->data);
    }

    public function update(UpdateTimelogBreak $request, $id)
    {
        $timeLogBreak = ProjectTimeLogBreak::findOrfail($id);
        $timeLog = ProjectTimeLog::findOrFail($timeLogBreak->project_time_log_id);
        $editTimelogPermission = user()->permission('edit_timelogs');

        abort_403(!(
            $editTimelogPermission == 'all'
        || ($editTimelogPermission == 'added' && $timeLog->added_by == user()->id)
        || ($editTimelogPermission == 'owned'
            && (($timeLog->project && $timeLog->project->client_id == user()->id) || $timeLog->user_id == user()->id)
            )
        || ($editTimelogPermission == 'both' && (($timeLog->project && $timeLog->project->client_id == user()->id) || $timeLog->user_id == user()->id || $timeLog->added_by == user()->id))
        ));

        $startTime = Carbon::parse($request->start_time)->format('Y-m-d') . ' ' . Carbon::parse($request->start_time)->format('H:i:s');
        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTime, $this->company->timezone)->setTimezone('UTC');
        $endTime = Carbon::parse($request->end_time)->format('Y-m-d') . ' ' . Carbon::parse($request->end_time)->format('H:i:s');
        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTime, $this->company->timezone)->setTimezone('UTC');

        $timeLogBreak->start_time = $startTime->format('Y-m-d H:i:s');
        $timeLogBreak->end_time = $endTime->format('Y-m-d H:i:s');

        $timeLogBreak->total_hours = $endTime->diffInHours($startTime);

        $timeLogBreak->total_minutes = $endTime->diffInMinutes($timeLogBreak->start_time);
        $timeLogBreak->save();

        // Calculate total minutes worked during the break
        $totalMinutesWorked = $timeLog->total_minutes - $timeLogBreak->total_minutes;

        // Determine hourly rate (replace this with your actual logic)
        $hourlyRate = $timeLog->hourly_rate; // Example hourly rate

        // Calculate earnings
        $earnings = ($hourlyRate * $totalMinutesWorked) / 60; // Convert minutes to hours and multiply by hourly rate

        // Update earnings column in the database
        $timeLog->earnings = $earnings;
        $timeLog->saveQuietly();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        ProjectTimeLogBreak::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

}
