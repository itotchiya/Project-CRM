<?php

namespace App\Observers;

use App\Models\Designation;
use App\Models\LeaveType;

class DesignationObserver
{

    public function creating(Designation $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    public function created(Designation $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {

                if (!is_null($leaveType->designation)) {
                    $designation = json_decode($leaveType->designation);
                    array_push($designation, $model->id);
                }
                else {
                    $designation = array($model->id);
                }

                $leaveType->designation = json_encode($designation);
                $leaveType->save();
            }
        }
    }

    public function deleted(Designation $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {

                if (!is_null($leaveType->department)) {

                    $designation = json_decode($leaveType->designation);

                    // Search value and delete
                    if (($key = array_search($model->id, $designation)) !== false) {
                        unset($designation[$key]);
                    }

                    $designationValues = array_values($designation);

                    $leaveType->department = json_encode($designationValues);
                    $leaveType->save();
                }

            }
        }
    }

}
