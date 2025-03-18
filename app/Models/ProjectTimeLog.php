<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\ProjectTimeLog
 *
 * @property int $id
 * @property string $start
 * @property string $name
 * @property int|null $project_id
 * @property int|null $task_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string $memo
 * @property string|null $total_hours
 * @property int|null $total_minutes
 * @property int|null $edited_by_user
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $hourly_rate
 * @property float $earnings
 * @property int $approved
 * @property int|null $approved_by
 * @property int|null $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $editor
 * @property-read mixed $duration
 * @property-read mixed $hours
 * @property-read mixed $icon
 * @property-read mixed $timer
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\Task|null $task
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereEditedByUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereMemo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereTotalHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereTotalMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereUserId($value)
 * @property-read \App\Models\User $user
 * @property string|null $total_break_minutes
 * @property-read \App\Models\ProjectTimeLogBreak|null $activeBreak
 * @property-read Collection|\App\Models\ProjectTimeLogBreak[] $breaks
 * @property-read int|null $breaks_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereTotalBreakMinutes($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $extras
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereCompanyId($value)
 * @property-read mixed $hours_only
 * @mixin \Eloquent
 */
class ProjectTimeLog extends BaseModel
{

    use Notifiable;
    use CustomFieldsTrait;
    use HasCompany;

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $with = ['breaks', 'activeBreak'];

    const CUSTOM_FIELD_MODEL = 'App\Models\ProjectTimeLog';

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user')->withoutGlobalScope(ActiveScope::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id')->withTrashed();
    }

    public function tasksOnlyTrashed(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id')->onlyTrashed();
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(ProjectTimeLogBreak::class, 'project_time_log_id');
    }

    public function activeBreak(): HasOne
    {
        return $this->hasOne(ProjectTimeLogBreak::class, 'project_time_log_id')->whereNull('end_time');
    }

    protected $appends = ['hours', 'duration', 'timer', 'hours_only'];

    public function getDurationAttribute()
    {
        $finishTime = now();

        if (!is_null($this->start_time)) {
            return $finishTime->diff($this->start_time)->format('%d days %H Hrs %i Mins %s Secs');
        }

        return '';
    }

    public function getHoursAttribute()
    {
        if (is_null($this->end_time)) {

            $totalMinutes = (($this->activeBreak) ? $this->activeBreak->start_time->diffInMinutes($this->start_time) : now()->diffInMinutes($this->start_time)) - $this->breaks->sum('total_minutes');

        }
        else {
            $totalMinutes = $this->total_minutes - $this->breaks->sum('total_minutes');
        }

        /** @phpstan-ignore-next-line */
        return CarbonInterval::formatHuman($totalMinutes);
    }

    public function getHoursOnlyAttribute()
    {
        if (is_null($this->end_time)) {

            $totalMinutes = (($this->activeBreak) ? $this->activeBreak->start_time->diffInMinutes($this->start_time) : now()->diffInMinutes($this->start_time)) - $this->breaks->sum('total_minutes');

        }
        else {
            $totalMinutes = $this->total_minutes - $this->breaks->sum('total_minutes');
        }

        $hours = floor($totalMinutes / 60);
        $minutes = ($totalMinutes % 60);

        return sprintf('%02d' . __('app.hrs') . ' %02d' . __('app.mins'), $hours, $minutes);
    }

    public function getTimerAttribute()
    {
        $finishTime = now();

        if (!is_null($this->activeBreak)) {
            $finishTime = $this->activeBreak->start_time;
        }

        $startTime = Carbon::parse($this->start_time);
        $days = $finishTime->diff($startTime)->format('%d');
        $hours = $finishTime->diff($startTime)->format('%H');

        if ($hours < 10) {
            $hours = '0' . $hours;
        }

        $minutes = $finishTime->diffInMinutes($startTime);
        $minutes = $minutes - $this->breaks->sum('total_minutes');

        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }

        $secs = $finishTime->diff($startTime)->format('%s');

        if ($secs < 10) {
            $secs = '0' . $secs;
        }

        $hours = floor((int)$minutes / 60);
        $minutes = ((int)$minutes % 60);

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    public static function dateWiseTimelogs($date, $userID = null)
    {
        $timelogs = ProjectTimeLog::with('breaks')->whereDate('start_time', $date);

        if (!is_null($userID)) {
            $timelogs = $timelogs->where('user_id', $userID);
        }

        return $timelogs = $timelogs->get();
    }

    public static function weekWiseTimelogs($startDate, $endDate, $userID = null)
    {
        $timelogs = ProjectTimeLog::whereBetween(DB::raw('DATE(`start_time`)'), [$startDate, $endDate]);

        if (!is_null($userID)) {
            $timelogs = $timelogs->where('user_id', $userID);
        }

        return $timelogs = $timelogs->sum('total_minutes');
    }

    public static function projectActiveTimers($projectId)
    {
        return ProjectTimeLog::with('user')->whereNull('end_time')
            ->where('project_id', $projectId)
            ->get();
    }

    public static function taskActiveTimers($taskId)
    {
        return ProjectTimeLog::with('user')->whereNull('end_time')
            ->where('task_id', $taskId)
            ->get();
    }

    public static function projectTotalHours($projectId)
    {
        return ProjectTimeLog::where('project_id', $projectId)
            ->sum('total_hours');
    }

    public static function projectTotalMinuts($projectId)
    {
        return ProjectTimeLog::where('project_id', $projectId)
            ->sum('total_minutes');
    }

    public static function memberActiveTimer($memberId)
    {
        return ProjectTimeLog::with('project')->where('user_id', $memberId)
            ->whereNull('end_time')
            ->first();
    }

    //    public static function selfActiveTimer()
    //    {
    //        $selfActiveTimer = ProjectTimeLog::doesnthave('activeBreak')
    //            ->where('user_id', user()->id)
    //            ->whereNull('end_time')
    //            ->first();
    //
    //        if (is_null($selfActiveTimer)) {
    //            $selfActiveTimer = ProjectTimeLog::with('activeBreak')
    //                ->where('user_id', user()->id)
    //                ->whereNull('end_time')
    //                ->orderByDesc('id')
    //                ->first();
    //        }
    //
    //        return $selfActiveTimer;
    //    }

    public static function selfActiveTimer()
    {
        return ProjectTimeLog::with('activeBreak')
            ->where('user_id', user()->id)
            ->whereNull('end_time')
            ->orderByDesc('id')
            ->first();
    }

    public static function totalActiveTimer()
    {
        return ProjectTimeLog::with('activeBreak')
            ->where('user_id', user()->id)
            ->whereNull('end_time')
            ->orderByDesc('id')
            ->get();
    }

}
