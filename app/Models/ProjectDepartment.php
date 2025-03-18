<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectDepartment extends Pivot
{
    protected $table = 'project_departments';
    protected $hidden = ['project_id', 'team_id'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

}
