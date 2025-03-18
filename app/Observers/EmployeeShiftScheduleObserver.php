<?php

namespace App\Observers;

use App\Events\EmployeeShiftScheduleEvent;
use App\Helper\Files;
use App\Models\EmployeeShift;
use App\Models\EmployeeShiftSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class EmployeeShiftScheduleObserver
{
    public static $isShiftRotation = false;

    public function saving(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user()) {
            $employeeShiftSchedule->last_updated_by = user()->id;
            $employeeShiftSchedule->remarks = request()->remarks;
        }
    }

    public function creating(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user()) {
            $employeeShiftSchedule->added_by = user()->id;
            $employeeShiftSchedule->shift_start_time = $employeeShiftSchedule->date->toDateString() . ' ' . $employeeShiftSchedule->shift->office_start_time;

            if (Carbon::parse($employeeShiftSchedule->shift->office_start_time)->gt(Carbon::parse($employeeShiftSchedule->shift->office_end_time))) {
                $employeeShiftSchedule->shift_end_time = $employeeShiftSchedule->date->addDay()->toDateString() . ' ' . $employeeShiftSchedule->shift->office_end_time;

            }
            else {
                $employeeShiftSchedule->shift_end_time = $employeeShiftSchedule->date->toDateString() . ' ' . $employeeShiftSchedule->shift->office_end_time;
            }

            $employeeShiftSchedule->remarks = request()->remarks;
        }
    }

    public function created(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user() && !self::$isShiftRotation) {
            event(new EmployeeShiftScheduleEvent($employeeShiftSchedule));
        }

        if (request()->hasFile('file')) {
            Files::deleteFile(request()->file, 'employee-shift-file/' . $employeeShiftSchedule->id);
            $fileName = Files::uploadLocalOrS3(request()->file, 'employee-shift-file/' . $employeeShiftSchedule->id);

            $employeeShiftSchedule->file = $fileName;
            $employeeShiftSchedule->saveQuietly();
        }
    }

    public function updating(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user()) {
            $employeeShiftSchedule->last_updated_by = user()->id;
        }

        if (!isRunningInConsoleOrSeeding() && user() && request()->employee_shift_id) {
            $shift = EmployeeShift::findOrFail(request()->employee_shift_id);

        }
        else {
            $shift = EmployeeShift::findOrFail($employeeShiftSchedule->employee_shift_id);
        }

        $employeeShiftSchedule->shift_start_time = $employeeShiftSchedule->date->toDateString() . ' ' . $shift->office_start_time;

        if (Carbon::parse($shift->office_start_time)->gt(Carbon::parse($shift->office_end_time))) {
            $employeeShiftSchedule->shift_end_time = $employeeShiftSchedule->date->addDay()->toDateString() . ' ' . $shift->office_end_time;

        }
        else {
            $employeeShiftSchedule->shift_end_time = $employeeShiftSchedule->date->toDateString() . ' ' . $shift->office_end_time;
        }
    }

    public function updated(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user() && $employeeShiftSchedule->isDirty('employee_shift_id')) {
            event(new EmployeeShiftScheduleEvent($employeeShiftSchedule));
        }
    }

    public function deleting(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if ($employeeShiftSchedule->file) {
            Files::deleteFile($employeeShiftSchedule->file, 'employee-shift-file/' . $employeeShiftSchedule->id);
            Files::deleteDirectory('employee-shift-file/' . $employeeShiftSchedule->id);
        }
    }

}
