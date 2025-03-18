<?php

namespace App\Traits;

use App\Models\DashboardWidget;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectStatusSetting;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;

/**
 *
 */
trait ProjectDashboard
{

    /**
     *
     * @return void
     */
    public function projectDashboard()
    {
        $this->viewProjectDashboard = user()->permission('view_project_dashboard');
        abort_403($this->viewProjectDashboard !== 'all');

        $this->pageTitle = 'app.projectDashboard';

        $this->startDate = (request('startDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('startDate')) : now($this->company->timezone)->startOfMonth();

        $this->endDate = (request('endDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('endDate')) : now($this->company->timezone);

        $todayDate = now(company()->timezone)->toDateString();
        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();

        $this->totalProject = Project::whereBetween(DB::raw('DATE(`start_date`)'), [$startDate, $endDate])->count();

        $hoursLogged = ProjectTimeLog::whereDate('start_time', '>=', $startDate)
            ->whereDate('end_time', '<=', $endDate)
            ->whereNotNull('project_id')
            ->where('approved', 1)
            ->sum('total_minutes');

        $breakMinutes = ProjectTimeLogBreak::join('project_time_logs', 'project_time_log_breaks.project_time_log_id', '=', 'project_time_logs.id')
            ->whereDate('project_time_logs.start_time', '>=', $startDate)
            ->whereDate('project_time_logs.end_time', '<=', $endDate)
            ->whereNotNull('project_time_logs.project_id')
            ->sum('project_time_log_breaks.total_minutes');

        $hoursLogged = $hoursLogged - $breakMinutes;

        // Convert total minutes to hours and minutes
        $hours = intdiv($hoursLogged, 60);
        $minutes = $hoursLogged % 60;

        // Format output based on hours and minutes
        $this->totalHoursLogged = $hours > 0
            ? $hours . 'h' . ($minutes > 0 ? ' ' . sprintf('%02dm', $minutes) : '')
            : ($minutes > 0 ? sprintf('%dm', $minutes) : '0s');

        /** @phpstan-ignore-next-line */

        if ($todayDate >= $startDate && $todayDate <= $endDate) {
            $this->totalOverdueProject = Project::whereNotNull('deadline')
                ->whereRaw('Date(projects.deadline) >= ?', [$startDate])
                ->whereRaw('Date(projects.deadline) < ?', [$todayDate])->count();
        }else{
            $this->totalOverdueProject = Project::whereNotNull('deadline')->whereBetween(DB::raw('DATE(`deadline`)'), [$startDate, $endDate])->count();
        }

        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-project-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(function ($value, $key) {
            return $value->status == '1';
        })->pluck('widget_name')->toArray();

        $this->pendingMilestone = ProjectMilestone::whereBetween(DB::raw('DATE(project_milestones.`created_at`)'), [$startDate, $endDate])
            ->with('project', 'currency')
            ->whereHas('project')
            ->where('status', 'incomplete')
            ->get();

        $this->statusWiseProject = $this->statusChartData($startDate, $endDate);

        $this->view = 'dashboard.ajax.project';
    }

    public function statusChartData($startDate, $endDate)
    {
        $labels = ProjectStatusSetting::where('status', 'active')->pluck('status_name');
        $data['labels'] = ProjectStatusSetting::where('status', 'active')->pluck('status_name');
        $data['colors'] = ProjectStatusSetting::where('status', 'active')->pluck('color');
        $data['values'] = [];

        foreach ($labels as $label) {
            $data['values'][] = Project::whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate])->where('status', $label)->count();
        }

        return $data;
    }

}
