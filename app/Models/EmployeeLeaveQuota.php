<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\EmployeeLeaveQuota
 *
 * @property int $id
 * @property int $user_id
 * @property int $leave_type_id
 * @property int $no_of_leaves
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LeaveType $leaveType
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereLeaveTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereNoOfLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereCompanyId($value)
 * @mixin \Eloquent
 */
class EmployeeLeaveQuota extends BaseModel
{

    protected $casts = [
        'carry_forward_status' => 'array',
    ];

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id')->withTrashed();
    }

}
