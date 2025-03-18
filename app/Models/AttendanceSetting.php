<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AttendanceSetting
 *
 * @property int $id
 * @property string $office_start_time
 * @property string $office_end_time
 * @property string|null $halfday_mark_time
 * @property int $late_mark_duration
 * @property int $clockin_in_day
 * @property string $employee_clock_in_out
 * @property string $office_open_days
 * @property string|null $ip_address
 * @property int|null $radius
 * @property string $radius_check
 * @property string $ip_check
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereClockinInDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereEmployeeClockInOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereHalfdayMarkTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereIpCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereLateMarkDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereOfficeEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereOfficeOpenDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereOfficeStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereRadiusCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereUpdatedAt($value)
 * @property int|null $alert_after
 * @property int $alert_after_status
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAlertAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAlertAfterStatus($value)
 * @property int $save_current_location
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereSaveCurrentLocation($value)
 * @property int|null $company_id
 * @property string $auto_clock_in
 * @property int|null $default_employee_shift
 * @property string $week_start_from
 * @property int $allow_shift_change
 * @property string $show_clock_in_button
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\EmployeeShift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAllowShiftChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAutoClockIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereDefaultEmployeeShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereShowClockInButton($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereWeekStartFrom($value)
 * @property string $restrict_clockin
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereRestrictClockin($value)
 * @property string $auto_clock_in_location
 * @property int $monthly_report
 * @property string|null $monthly_report_roles
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAutoClockInLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereMonthlyReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereMonthlyReportRoles($value)
 * @property string|null $early_clock_in
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereEarlyClockIn($value)
 * @mixin \Eloquent
 */
class AttendanceSetting extends BaseModel
{

    use HasCompany;
    protected $fillable = ['qr_enable', 'default_employee_shift'];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(EmployeeShift::class, 'default_employee_shift');
    }

}
