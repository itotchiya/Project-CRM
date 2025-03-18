<?php

namespace App\Console\Commands;

use App\Models\EmployeeLeaveQuotaHistory;
use App\Models\User;
use Illuminate\Console\Command;

class CreateEmployeeLeaveQuotaHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-employee-leave-quota-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $employees = User::withoutGlobalScopes()->onlyEmployee()->with(['leaveTypes', 'leaveTypes.leaveType'])->get();
        $employeeLeaveQuotaHistories = [];

        foreach ($employees as $employee) {
            foreach ($employee->leaveTypes as $leaveQuota) {
                if ($leaveQuota->leaveType && ($leaveQuota->leaveType->leaveTypeCondition($leaveQuota->leaveType, $employee)))
                {
                    $employeeLeaveQuotaHistories[] = [
                        'user_id' => $employee->id,
                        'leave_type_id' => $leaveQuota->leave_type_id,
                        'no_of_leaves' => $leaveQuota->no_of_leaves,
                        'leaves_used' => $leaveQuota->leaves_used,
                        'leaves_remaining' => $leaveQuota->leaves_remaining,
                        'overutilised_leaves' => $leaveQuota->overutilised_leaves,
                        'carry_forward_leaves' => $leaveQuota->carry_forward_leaves,
                        'unused_leaves' => $leaveQuota->unused_leaves,
                        'for_month' => now()->subMonth()->format('Y-m-01'),
                    ];
                }

            }
        }

        EmployeeLeaveQuotaHistory::insert($employeeLeaveQuotaHistories);
    }

}
