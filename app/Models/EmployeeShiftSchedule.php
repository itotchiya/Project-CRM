<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\EmployeeShiftSchedule
 *
 * @property string|null $color
 * @property-read \App\Models\EmployeeShift $shift
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $date
 * @property int $employee_shift_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $shift_start_time
 * @property \Illuminate\Support\Carbon|null $shift_end_time
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EmployeeShiftChangeRequest|null $pendingRequestChange
 * @property-read \App\Models\EmployeeShiftChangeRequest|null $requestChange
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereEmployeeShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereShiftEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereShiftStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereUserId($value)
 * @property string $file
 * @property-read mixed $download_file_url
 * @property-read mixed $file_url
 * @property-read \App\Models\EmployeeShiftSchedule|null $dates
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereFile($value)
 * @mixin \Eloquent
 */
class EmployeeShiftSchedule extends BaseModel
{

    use HasFactory;

    protected $casts = [
        'date' => 'datetime',
        'shift_start_time' => 'datetime',
        'shift_end_time' => 'datetime',
    ];

    protected $appends = ['file_url', 'download_file_url'];

    protected $guarded = ['id'];

    protected $with = ['shift'];

    public function getFileUrlAttribute()
    {
        return ($this->file) ? asset_url_local_s3('employee-shift-file/'. $this->id.'/' . $this->file) : '';
    }

    public function getDownloadFileUrlAttribute()
    {
        return ($this->file) ? asset_url_local_s3('employee-shift-file/'. $this->id.'/' . $this->file) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(EmployeeShift::class, 'employee_shift_id');
    }

    public function requestChange(): HasOne
    {
        return $this->hasOne(EmployeeShiftChangeRequest::class, 'shift_schedule_id');
    }

    public function pendingRequestChange(): HasOne
    {
        return $this->hasOne(EmployeeShiftChangeRequest::class, 'shift_schedule_id')->where('status', 'waiting');
    }

}
