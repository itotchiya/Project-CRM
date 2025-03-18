<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftRotation extends BaseModel
{

    use HasCompany;

    protected $table = 'employee_shift_rotations';

    public function scopeActive(Builder $query): void
    {
        $query->where('employee_shift_rotations.status', 'active');
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(ShiftRotationSequence::class, 'employee_shift_rotation_id', 'id');
    }

    public function automateShifts(): HasMany
    {
        return $this->hasMany(AutomateShift::class, 'employee_shift_rotation_id', 'id');
    }

}
