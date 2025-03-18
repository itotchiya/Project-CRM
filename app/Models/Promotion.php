<?php

namespace App\Models;

use App\Traits\HasCompany;

class Promotion extends BaseModel
{
    use HasCompany;

    public function employee()
    {
        return $this->belongsTo(User::class);
    }

    public function currentDesignation()
    {
        return $this->belongsTo(Designation::class, 'current_designation_id');
    }

    public function previousDesignation()
    {
        return $this->belongsTo(Designation::class, 'previous_designation_id');
    }

    public function currentDepartment()
    {
        return $this->belongsTo(Team::class, 'current_department_id');
    }

    public function previousDepartment()
    {
        return $this->belongsTo(Team::class, 'previous_department_id');
    }

    public static function employeePromotions($userId)
    {
        return self::where('employee_id', $userId)
            ->whereNotNull('current_designation_id')
            ->whereNotNull('previous_designation_id')
            ->whereColumn('current_designation_id', '!=', 'previous_designation_id')
            ->with(['employee', 'currentDesignation', 'previousDesignation'])
            ->get();
    }

}
