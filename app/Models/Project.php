<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Project
 *
 * @property int $id
 * @property string $project_name
 * @property string|null $project_summary
 * @property int|null $project_admin
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectNote[] $notes
 * @property int|null $category_id
 * @property int|null $client_id
 * @property int|null $team_id
 * @property string|null $feedback
 * @property string $manual_timelog
 * @property string $client_view_task
 * @property string $allow_client_notification
 * @property int $completion_percent
 * @property string $calculate_task_progress
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property float|null $project_budget
 * @property int|null $currency_id
 * @property float|null $hours_allocated
 * @property string $status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\ProjectCategory|null $category
 * @property-read \App\Models\User|null $client
 * @property-read \App\Models\ClientDetails|null $clientdetails
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Discussion[] $discussions
 * @property-read int|null $discussions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Expense[] $expenses
 * @property-read int|null $expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $is_project_admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Issue[] $issues
 * @property-read int|null $issues_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectMember[] $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $projectMembers
 * @property-read int|null $members_many_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectMilestone[] $milestones
 * @property-read int|null $milestones_count
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\ProjectRating|null $rating
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLog[] $times
 * @property-read int|null $times_count
 * @method static \Illuminate\Database\Eloquent\Builder|Project canceled()
 * @method static \Illuminate\Database\Eloquent\Builder|Project completed()
 * @method static \Database\Factories\ProjectFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Project finished()
 * @method static \Illuminate\Database\Eloquent\Builder|Project inProcess()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project notStarted()
 * @method static \Illuminate\Database\Eloquent\Builder|Project onHold()
 * @method static \Illuminate\Database\Query\Builder|Project onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Project overdue()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereAllowClientNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCalculateTaskProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereClientViewTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCompletionPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereHoursAllocated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereManualTimelog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Project withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Project withoutTrashed()
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereHash($value)
 * @property int $public
 * @method static \Illuminate\Database\Eloquent\Builder|Project wherePublic($value)
 * @property int|null $company_id
 * @property string|null $project_short_code
 * @property int $enable_miroboard
 * @property string|null $miro_board_id
 * @property int $client_access
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereClientAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereEnableMiroboard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereMiroBoardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectShortCode($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contract> $contracts
 * @property-read int|null $contracts_count
 * @property-read int|null $project_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionNote
 * @property-read int|null $mention_note_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectMilestone> $incompleteMilestones
 * @property-read int|null $incomplete_milestones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionProject
 * @property-read int|null $mention_project_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read \App\Models\Project|null $due_date
 * @mixin \Eloquent
 */
class Project extends BaseModel
{

    use CustomFieldsTrait, HasFactory;
    use SoftDeletes;
    use HasCompany;

    protected $casts = [
        'start_date' => 'datetime',
        'deadline' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $guarded = ['id'];
    protected $with = [];
    protected $appends = ['isProjectAdmin'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Project';

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function projectAdmin()
    {
        return $this->belongsTo(User::class, 'project_admin');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function projectMembersWithoutScope(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->using(ProjectMember::class)->withoutGlobalScope(ActiveScope::class);
    }

    public function projectMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->using(ProjectMember::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id')->orderByDesc('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class, 'project_id')->orderByDesc('id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'project_id')->orderByDesc('id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'project_id')->orderByDesc('id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'project_id')->orderByDesc('id');
    }

    public function times(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'project_id')->with('breaks')->orderByDesc('id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id')->orderByDesc('id');
    }

    public function incompleteMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id')->whereNot('status', 'complete')->orderByDesc('id');
    }

    public function completedMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id')->where('status', 'complete')->orderByDesc('id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'project_id')->orderByDesc('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ProjectNote::class, 'project_id')->orderByDesc('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'project_id')->orderByDesc('id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class, 'project_id')->orderByDesc('id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(ProjectRating::class);
    }

    /**
     * @return bool
     */
    public function checkProjectUser()
    {
        return ProjectMember::where('project_id', $this->id)
            ->where('user_id', user()->id)
            ->exists();
    }

    /**
     * @return bool
     */
    public function checkProjectClient()
    {
        return Project::where('id', $this->id)
            ->where('client_id', user()->id)
            ->exists();

    }

    public static function clientProjects($clientId)
    {
        return Project::where('client_id', $clientId)->get();
    }

    /**
     * @param boolean $notFinished
     * Search Parameter is passed the get only search results and 20
     * @return \Illuminate\Support\Collection
     */
    public static function allProjects($notFinished = false)
    {
        $projects = Project::query();

        if ($notFinished) {
            $projects->notFinished();
        }

        $projects = $projects->leftJoin('project_members', 'project_members.project_id', 'projects.id')
            ->select('projects.*')
            ->orderBy('project_name');


        if (!isRunningInConsoleOrSeeding()) {

            if (user()->permission('view_projects') == 'added') {
                $projects->where('projects.added_by', user()->id)->orWhere('projects.public', 1);
            }

            if (user()->permission('view_projects') == 'added' && in_array('client', user_roles())) {
                $projects->where('projects.added_by', user()->id);
            }

            if (user()->permission('view_projects') == 'both') {
                $projects->where('projects.added_by', user()->id)->orWhere('project_members.user_id', user()->id)->orWhere('projects.public', 1);
            }

            if (user()->permission('view_projects') == 'both' && in_array('client', user_roles())) {
                $projects->where('projects.added_by', user()->id)->orWhere('projects.client_id', user()->id);
            }

            if (user()->permission('view_projects') == 'owned' && in_array('employee', user_roles())) {
                $projects->where('project_members.user_id', user()->id)->orWhere('projects.public', 1);
            }

            if (user()->permission('view_projects') == 'owned' && in_array('client', user_roles())) {
                $projects->where('projects.client_id', user()->id);
            }

            if (user()->permission('view_projects') == 'all' && in_array('client', user_roles())) {
                $projects->where('projects.client_id', user()->id);
            }
        }

        $projects = $projects->groupBy('projects.id');

        // @codingStandardsIgnoreStart
        //        if ($search !== '') {
        //            return $projects->where('project_name', 'like', '%' . $search . '%')
        //                ->take(GlobalSetting::SELECT2_SHOW_COUNT)
        //                ->get();
        //        }
        // @codingStandardsIgnoreEnd

        return $projects->get();
    }

    public static function allProjectsHavingClient()
    {
        $projects = Project::with('currency')->leftJoin('project_members', 'project_members.project_id', 'projects.id')
            ->whereNotNull('client_id')
            ->select('projects.*')
            ->orderBy('project_name');

        if (!isRunningInConsoleOrSeeding()) {

            if (user()->permission('view_projects') == 'added') {
                $projects->where('projects.added_by', user()->id);
            }

            if (user()->permission('view_projects') == 'owned' && in_array('employee', user_roles())) {
                $projects->where('project_members.user_id', user()->id);
            }

            if (user()->permission('view_projects') == 'owned' && in_array('client', user_roles())) {
                $projects->where('projects.client_id', user()->id);
            }
        }

        return $projects->groupBy('projects.id')->get();
    }

    public static function byEmployee($employeeId)
    {
        return Project::join('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.user_id', $employeeId)
            ->select('projects.*')
            ->get();
    }

    public function scopeCompleted($query)
    {
        return $query->where('completion_percent', '100');
    }

    public function scopeInProcess($query)
    {
        return $query->where('status', 'in progress');
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', 'on hold');
    }

    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }

    public function scopeNotFinished($query)
    {
        return $query->where('status', '<>', 'finished');
    }

    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not started');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopeOverdue($query)
    {
        $setting = company();

        return $query->where('completion_percent', '<>', '100')
            ->where('deadline', '<', Carbon::today()->timezone($setting->timezone));
    }

    public function getIsProjectAdminAttribute()
    {
        if (auth()->user() && $this->project_admin == user()->id) {
            return true;
        }

        return false;
    }

    public function pinned()
    {
        $pin = Pinned::where('user_id', user()->id)->where('project_id', $this->id)->first();

        if (!is_null($pin)) {
            return true;
        }

        return false;
    }

    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    public function mentionProject(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'project_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(ProjectDepartment::class, 'project_id');
    }

    public function projectDepartments(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'project_departments')->using(ProjectDepartment::class);
    }

}
