<?php

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::whenTableDoesntHaveColumn('employee_leave_quotas', 'leaves_used', function (Blueprint $table) {
            $table->after('no_of_leaves', function ($table) {
                $table->double('leaves_used')->default(0);
                $table->double('leaves_remaining')->default(0);
            });
        });

        LeaveType::whereNull('unused_leave')->update(['unused_leave' => 'carry forward']);

        $employees = User::withoutGlobalScopes()->onlyEmployee()->get();

        foreach ($employees as $employee) {

            $leaveQuotas = $employee->leaveTypes;

            foreach ($leaveQuotas as $leaveQuota) {
                $leave = $this->byUser($employee, $leaveQuota->leave_type_id);
                $leaveTaken = $leave ? ($leave->leavesCount ? $leave->leavesCount->count - ($leave->leavesCount->halfday * 0.5) : 0) : 0;
                $leaveQuota->leaves_remaining = $leaveQuota->no_of_leaves - $leaveTaken;
                $leaveQuota->leaves_used = $leaveTaken;
                $leaveQuota->save();
            }
        }

    }

    public function byUser(User $user, $leaveTypeId = null, $status = array('approved'), $leaveDate = null)
    {
        $setting = $user->company;

        if (!is_null($leaveDate)) {
            $leaveDate = Carbon::createFromFormat($setting->date_format, $leaveDate);

        }
        else {
            $leaveDate = Carbon::createFromFormat('d-m-Y', '01-'.$setting->year_starts_from.'-'.now($setting->timezone)->year)->startOfMonth();
        }

        if ($user->employee->first()) {
            if ($setting->leaves_start_from == 'joining_date') {
                $currentYearJoiningDate = Carbon::parse($user->employee->first()->joining_date->format((now($setting->timezone)->year) . '-m-d'));

                if ($currentYearJoiningDate->isFuture()) {
                    $currentYearJoiningDate->subYear();
                }

                $leaveTypes = LeaveType::with(['leavesCount' => function ($q) use ($user, $currentYearJoiningDate, $status) {
                    $q->where('leaves.user_id', $user->id);
                    $q->whereBetween('leaves.leave_date', [$currentYearJoiningDate->copy()->toDateString(), $currentYearJoiningDate->copy()->addYear()->toDateString()]);
                    $q->whereIn('leaves.status', $status);
                }])->select('leave_types.*', 'employee_details.notice_period_start_date', 'employee_details.probation_end_date',
                'employee_details.department_id as employee_department', 'employee_details.designation_id as employee_designation',
                'employee_details.marital_status as maritalStatus', 'users.gender as usergender', 'employee_details.joining_date')
                ->join('employee_leave_quotas', 'employee_leave_quotas.leave_type_id', 'leave_types.id')
                ->join('users', 'users.id', 'employee_leave_quotas.user_id')
                ->join('employee_details', 'employee_details.user_id', 'users.id')->where('users.id', $user->id);

                return $leaveTypes->where('leave_types.id', $leaveTypeId)->first();

            }
            else {
                $leaveTypes = LeaveType::with(['leavesCount' => function ($q) use ($user, $status, $leaveDate) {
                    $q->where('leaves.user_id', $user->id);
                    $q->whereBetween('leaves.leave_date', [$leaveDate->copy()->toDateString(), $leaveDate->copy()->addYear()->toDateString()]);
                    $q->whereIn('leaves.status', $status);
                }])->select('leave_types.*', 'employee_details.notice_period_start_date', 'employee_details.probation_end_date',
                'employee_details.department_id as employee_department', 'employee_details.designation_id as employee_designation',
                'employee_details.marital_status as maritalStatus', 'users.gender as usergender', 'employee_details.joining_date')
                ->join('employee_leave_quotas', 'employee_leave_quotas.leave_type_id', 'leave_types.id')
                ->join('users', 'users.id', 'employee_leave_quotas.user_id')
                ->join('employee_details', 'employee_details.user_id', 'users.id')->where('users.id', $user->id);
            }

            return $leaveTypes->where('leave_types.id', $leaveTypeId)->first();
        }

        return null;

    }

};
