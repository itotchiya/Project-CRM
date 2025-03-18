<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\EmployeeShiftChangeRequest
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $shift_schedule_id
 * @property int $employee_shift_id
 * @property string $status
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EmployeeShift $shift
 * @property-read \App\Models\EmployeeShiftSchedule $shiftSchedule
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereEmployeeShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereShiftScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereUpdatedAt($value)
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereCompanyId($value)
 * @mixin \Eloquent
 */
class EmployeeShiftChangeRequest extends BaseModel
{

    use HasFactory, HasCompany;

    protected $guarded = ['id'];

    public function shiftSchedule(): BelongsTo
    {
        return $this->belongsTo(EmployeeShiftSchedule::class, 'shift_schedule_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(EmployeeShift::class, 'employee_shift_id');
    }

}
