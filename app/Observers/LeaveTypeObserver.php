<?php

namespace App\Observers;

use App\Models\LeaveType;
use Illuminate\Support\Carbon;
use App\Models\EmployeeDetails;
use App\Models\EmployeeLeaveQuota;
use Illuminate\Support\Facades\Artisan;

class LeaveTypeObserver
{

    public function creating(LeaveType $leaveType)
    {
        if (company()) {
            $leaveType->company_id = company()->id;
        }
    }

    public function created(LeaveType $leaveType)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $employees = EmployeeDetails::select('id', 'user_id', 'joining_date')->get();
            $settings = company();

            foreach ($employees as $key => $employee) {
                Artisan::call('app:recalculate-leaves-quotas ' . $settings->id . ' ' . $employee->user_id . ' ' .$leaveType->id);
            }
        }
    }

    public function updated(LeaveType $leaveType)
    {
      
        if (
                request()->has('restore') && request()->restore == 'restore' ||
                 ((session()->has('old_leaves') && session('old_leaves') == $leaveType->no_of_leaves) && (session()->has('old_leavetype') && session('old_leavetype') == $leaveType->leavetype))
            ) {

            if (session()->has('old_leaves')) {
                session()->forget('old_leaves');
            }

            return true;
        }
        
        if (!isRunningInConsoleOrSeeding()) {

            if (!$leaveType->isDirty('over_utilization')) {
           
                $employeeLeaveQuotaUserIds = EmployeeLeaveQuota::where('leave_type_id', $leaveType->id)->where('leave_type_impact', 1)
                    ->pluck('user_id')
                    ->toArray();
                   
                $employees = EmployeeDetails::select('id', 'user_id', 'joining_date')->whereNotIn('user_id', $employeeLeaveQuotaUserIds)->get();
    
                $settings = company();
    
                foreach ($employees as $employee) {
                    
                    Artisan::call('app:recalculate-leaves-quotas ' . $settings->id . ' ' . $employee->user_id . ' ' .$leaveType->id);

                }

                $keysToForget = ['old_leaves', 'old_leavetype'];

                foreach ($keysToForget as $key) {
                    if (session()->has($key)) {
                        session()->forget($key);
                    }
                }
            }
        }
    }

}
