<?php

namespace App\Observers;

use App\Models\LeaveType;
use App\Models\Team;

class TeamObserver
{

    public function creating(Team $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }

    }

    public function created(Team $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {
                if (!is_null($leaveType->department)) {
                    $department = json_decode($leaveType->department);
                    array_push($department, $model->id);
                }
                else {
                    $department = array($model->id);
                }

                $leaveType->department = json_encode($department);
                $leaveType->save();
            }
        }
    }

    public function deleted(Team $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {

                if (!is_null($leaveType->department)) {
                    $department = json_decode($leaveType->department);

                    // Search value and delete
                    if (($key = array_search($model->id, $department)) !== false) {
                        unset($department[$key]);
                    }

                    $departmentValues = array_values($department);

                    $leaveType->department = json_encode($departmentValues);
                    $leaveType->save();
                }

            }
        }
    }

}
