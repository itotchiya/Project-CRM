<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Class Holiday
 *
 * @package App\Models
 * @property int $id
 * @property \Illuminate\Support\Carbon $date
 * @property string|null $occassion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $addedBy
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday query()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereOccassion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUpdatedAt($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereEventId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCompanyId($value)
 * @property-read \App\Models\Holiday|null $hdate
 * @property-read \App\Models\Leave|null $ldate
 * @mixin \Eloquent
 */
class Holiday extends BaseModel
{

    use HasCompany;

    const SUNDAY = 0;

    const MONDAY = 1;

    const TUESDAY = 2;

    const WEDNESDAY = 3;

    const THURSDAY = 4;

    const FRIDAY = 5;

    const SATURDAY = 6;

    // Don't forget to fill this array
    protected $fillable = ['date', 'occassion'];

    protected $guarded = ['id'];
    protected $casts = [
        'date' => 'datetime',
    ];

    public static function getHolidayByDates($startDate, $endDate, $userId = null)
    {

        $holiday = Holiday::select(DB::raw('DATE_FORMAT(date, "%Y-%m-%d") as holiday_date'), 'occassion')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate);

        if (is_null($userId)) {
            return $holiday->get();
        }

        $user = User::find($userId);

        if ($user) {
            $holiday = $holiday->where(function ($query) use ($user) {
                $query->where(function ($subquery) use ($user) {
                    $subquery->where(function ($q) use ($user) {
                        $q->where('department_id_json', 'like', '%"' . $user->employeeDetail->department_id . '"%')
                            ->orWhereNull('department_id_json');
                    });
                    $subquery->where(function ($q) use ($user) {
                        $q->where('designation_id_json', 'like', '%"' . $user->employeeDetail->designation_id . '"%')
                            ->orWhereNull('designation_id_json');
                    });
                    $subquery->where(function ($q) use ($user) {
                        $q->where('employment_type_json', 'like', '%"' . $user->employeeDetail->employment_type . '"%')
                            ->orWhereNull('employment_type_json');
                    });
                });
            });
        }

        return $holiday->get();
    }

    public static function checkHolidayByDate($date)
    {
        return Holiday::Where('date', $date)->first();
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    public static function weekMap($format = 'l')
    {
        return [
            Holiday::MONDAY => now()->startOfWeek(1)->translatedFormat($format),
            Holiday::TUESDAY => now()->startOfWeek(2)->translatedFormat($format),
            Holiday::WEDNESDAY => now()->startOfWeek(3)->translatedFormat($format),
            Holiday::THURSDAY => now()->startOfWeek(4)->translatedFormat($format),
            Holiday::FRIDAY => now()->startOfWeek(5)->translatedFormat($format),
            Holiday::SATURDAY => now()->startOfWeek(6)->translatedFormat($format),
            Holiday::SUNDAY => now()->startOfWeek(7)->translatedFormat($format),
        ];
    }

    public static function designation($ids)
    {
        $designation = null;

        if ($ids != null) {
            $designation = Designation::whereIn('id', $ids)->pluck('name')->toArray();
        }

        return $designation;
    }

    public static function department($ids)
    {
        $department = null;

        if ($ids != null) {
            $department = Team::whereIn('id', $ids)->pluck('team_name')->toArray();
        }

        return $department;
    }

    public function employee()
    {
        return $this->hasMany(EmployeeDetails::class, 'user_id');
    }

    public function employeeDetails()
    {
        return $this->hasOne(EmployeeDetails::class, 'user_id');
    }

}
