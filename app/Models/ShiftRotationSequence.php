<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftRotationSequence extends BaseModel
{

    use HasFactory;

    protected $table = 'shift_rotation_sequences';

    public function rotation(): BelongsTo
    {
        return $this->belongsTo(ShiftRotation::class, 'employee_shift_rotation_id', 'id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(EmployeeShift::class, 'employee_shift_id', 'id');
    }

}
