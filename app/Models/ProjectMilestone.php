<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCompany;


/**
 * App\Models\ProjectMilestone
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $currency_id
 * @property string $milestone_title
 * @property string $summary
 * @property float $cost
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $invoice_created
 * @property int|null $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $icon
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereInvoiceCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereMilestoneTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereUpdatedAt($value)
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereStartDate($value)
 * @mixin \Eloquent
 */
class ProjectMilestone extends BaseModel
{

    use HasCompany;
    
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'milestone_id');
    }

    public function completeTasks(): HasMany
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();
        return $this->hasMany(Task::class, 'milestone_id')->where('tasks.board_column_id', $taskBoardColumn->id);
    }

    public function completionPercent()
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();
        $tasks = $this->tasks()->count();
        $completedTasks = $this->tasks()->where('tasks.board_column_id', $taskBoardColumn->id)->count();
        return ($completedTasks / $tasks) * 100;
    }

}
