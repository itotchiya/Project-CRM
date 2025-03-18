<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Leave
 *
 * @property int $id
 * @property int $user_id
 * @property int $leave_type_id
 * @property int $count
 * @property int $halfday
 * @property string $duration
 * @property \Illuminate\Support\Carbon $leave_date
 * @property string $reason
 * @property string $status
 * @property string|null $reject_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $paid
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $date
 * @property-read mixed $icon
 * @property-read mixed $leaves_taken_count
 * @property-read \App\Models\LeaveType $type
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\LeaveFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave query()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereLeaveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereLeaveTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereRejectReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUserId($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereEventId($value)
 * @property int|null $company_id
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $half_day_type
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereHalfDayType($value)
 * @property string|null $manager_status_permission
 * @property string|null $approve_reason
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereApproveReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereManagerStatusPermission($value)
 * @property string|null $unique_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeaveFile> $files
 * @property-read int|null $files_count
 * @property-read \App\Models\Leave|null $ldate
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUniqueId($value)
 * @mixin \Eloquent
 */
class Leave extends BaseModel
{

    use HasFactory;
    use HasCompany;

    protected $casts = [
        'leave_date' => 'datetime',
        'approved_at' => 'datetime',
    ];
    protected $guarded = ['id'];
    protected $appends = ['date']; // Being used in attendance

    public function getDateAttribute()
    {
        return $this->leave_date->toDateString();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails', 'role');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id')->withTrashed();
    }

    public function getLeavesTakenCountAttribute()
    {
        $userId = $this->user_id;
        $setting = company();
        $user = User::withoutGlobalScope(ActiveScope::class)->withOut('clientDetails', 'role')->findOrFail($userId);
        $currentYearJoiningDate = Carbon::parse($user->employee[0]->joining_date->format((now(company()->timezone)->year) . '-m-d'));

        if ($currentYearJoiningDate->isFuture()) {
            $currentYearJoiningDate->subYear();
        }

        $leaveFrom = $currentYearJoiningDate->copy()->toDateString();
        $leaveTo = $currentYearJoiningDate->copy()->addYear()->toDateString();

        if ($setting->leaves_start_from !== 'joining_date') {
            $leaveStartYear = Carbon::parse(now()->format((now(company()->timezone)->year) . '-' . company()->year_starts_from . '-01'));

            if ($leaveStartYear->isFuture()) {
                $leaveStartYear = $leaveStartYear->subYear();
            }

            $leaveFrom = $leaveStartYear->copy()->toDateString();
            $leaveTo = $leaveStartYear->copy()->addYear()->toDateString();
        }

        $fullDay = Leave::where('user_id', $userId)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('duration', '<>', 'half day')
            ->count();

        $halfDay = Leave::where('user_id', $userId)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('duration', 'half day')
            ->count();

        return ($fullDay + ($halfDay / 2));

    }

    public static function byUserCount($user, $year = null)
    {
        $setting = company();

        if (!$user instanceof User) {
            $user = User::withoutGlobalScope(ActiveScope::class)->withOut('clientDetails', 'role')->findOrFail($user);
        }

        $leaveFrom = (is_null($year)) ? Carbon::createFromFormat('d-m-Y', '01-'.company()->year_starts_from.'-'.now(company()->timezone)->year)->startOfMonth()->toDateString() : Carbon::createFromFormat('d-m-Y', '01-'.company()->year_starts_from.'-'.$year)->startOfMonth()->toDateString();
        $leaveTo = Carbon::parse($leaveFrom)->addYear()->subDay()->toDateString();

        if ($setting->leaves_start_from == 'joining_date' && isset($user->employee[0])) {
            $currentYearJoiningDate = Carbon::parse($user->employee[0]->joining_date->format((now(company()->timezone)->year) . '-m-d'));

            if ($currentYearJoiningDate->isFuture()) {
                $currentYearJoiningDate->subYear();
            }

            $leaveFrom = $currentYearJoiningDate->copy()->toDateString();
            $leaveTo = $currentYearJoiningDate->copy()->addYear()->toDateString();
        }

        $fullDay = Leave::where('user_id', $user->id)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('duration', '<>', 'half day')
            ->get();

        $halfDay = Leave::where('user_id', $user->id)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('duration', 'half day')
            ->get();

        return (count($fullDay) + (count($halfDay) / 2));
    }

    public function files(): HasMany
    {
        return $this->hasMany(LeaveFile::class, 'leave_id')->orderByDesc('id');
    }

}
