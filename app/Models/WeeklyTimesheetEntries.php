<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCompany;

class WeeklyTimesheetEntries extends BaseModel
{
    use HasFactory;
    use HasCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function weeklyTimesheet(): BelongsTo
    {
        return $this->belongsTo(WeeklyTimesheet::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
