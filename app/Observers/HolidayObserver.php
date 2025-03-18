<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Holiday;
use App\Events\HolidayEvent;
use App\Models\EmployeeShiftSchedule;

class HolidayObserver
{

    public function saving(Holiday $holiday)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $holiday->last_updated_by = user()->id;
        }
    }

    public function creating(Holiday $holiday)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $holiday->added_by = user()->id;
        }

        if (company()) {
            $holiday->company_id = company()->id;
        }
    }

    public function created(Holiday $holiday)
    {
        if (request()->notification_sent == 'yes') {
            $users = User::join('employee_details', 'employee_details.user_id', '=', 'users.id')
                ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
                ->select('users.id', 'users.company_id', 'users.name', 'users.email', 'users.created_at', 'users.image', 'designations.name as designation_name', 'users.email_notifications', 'users.mobile', 'users.country_id', 'users.status');

            if ($holiday->department_id_json
                && $holiday->department_id_json != null
                && $holiday->department_id_json != '[]') {
                $users->whereIn('employee_details.department_id', json_decode($holiday->department_id_json));
            }

            if ($holiday->designation_id_json
                && $holiday->designation_id_json != null
                && $holiday->designation_id_json != '[]') {
                $users->whereIn('employee_details.designation_id', json_decode($holiday->designation_id_json));
            }

            if ($holiday->employment_type_json
                && $holiday->employment_type_json != null
                && $holiday->employment_type_json != '[]') {
                $users->whereIn('employee_details.employment_type', json_decode($holiday->employment_type_json));
            }

            $notifyUser = $users->groupBy('users.id')->get();
            event(new HolidayEvent($holiday, request()->date, request()->occassion, $notifyUser));
        }

        EmployeeShiftSchedule::whereDate('date', $holiday->date)->delete();
    }


    public function updated(Holiday $holiday)
    {
        EmployeeShiftSchedule::whereDate('date', $holiday->date)->delete();
    }

}
