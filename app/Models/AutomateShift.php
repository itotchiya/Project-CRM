<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomateShift extends BaseModel
{

    use HasFactory;

    protected $table = 'automate_shifts';

    public function rotation(): BelongsTo
    {
        return $this->belongsTo(ShiftRotation::class, 'employee_shift_rotation_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(ShiftRotationSequence::class, 'employee_shift_rotation_id');
    }

}
