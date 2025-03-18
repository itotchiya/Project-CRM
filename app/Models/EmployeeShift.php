<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\EmployeeShift
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $shift_name
 * @property string $shift_short_code
 * @property string $color
 * @property string $office_start_time
 * @property string $office_end_time
 * @property string $auto_clock_out_time
 * @property string|null $halfday_mark_time
 * @property int $late_mark_duration
 * @property int $clockin_in_day
 * @property string $office_open_days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereClockinInDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereHalfdayMarkTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereLateMarkDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereOfficeEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereOfficeOpenDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereOfficeStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereShiftName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereShiftShortCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereUpdatedAt($value)
 * @property-read \App\Models\Company|null $company
 * @property string|null $early_clock_in
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereEarlyClockIn($value)
 * @mixin \Eloquent
 */
class EmployeeShift extends BaseModel
{

    use HasFactory, HasCompany;

    protected $guarded = ['id'];

    public function employeeShiftSchedules(): HasMany
    {
        return $this->hasMany(EmployeeShiftSchedule::class, 'employee_shift_id', 'id');
    }

}
