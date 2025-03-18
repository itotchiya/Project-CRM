<?php

use App\Models\EmployeeLeaveQuotaHistory;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employee_leave_quota_histories')) {
            return;
        }

        Schema::create('employee_leave_quota_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index('employee_leave_quotas_user_id_foreign');
            $table->unsignedInteger('leave_type_id')->index('employee_leave_quotas_leave_type_id_foreign');
            $table->foreign(['leave_type_id'])->references(['id'])->on('leave_types')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->double('no_of_leaves');
            $table->double('leaves_used')->default(0);
            $table->double('leaves_remaining')->default(0);
            $table->date('for_month');
            $table->timestamps();
        });

        $employees = User::withoutGlobalScopes()->onlyEmployee()->with(['leaveTypes', 'leaveTypes.leaveType'])->get();
        $employeeLeaveQuotaHistories = [];

        foreach ($employees as $employee) {
            foreach ($employee->leaveTypes as $leaveQuota) {
                if (($leaveQuota->leaveType->leaveTypeCondition($leaveQuota->leaveType, $employee)))
                {
                    $employeeLeaveQuotaHistories[] = [
                        'user_id' => $employee->id,
                        'leave_type_id' => $leaveQuota->leave_type_id,
                        'no_of_leaves' => $leaveQuota->no_of_leaves,
                        'leaves_used' => $leaveQuota->leaves_used,
                        'leaves_remaining' => $leaveQuota->leaves_remaining,
                        'for_month' => now()->subMonth()->format('Y-m-01'),
                    ];
                }

            }
        }

        EmployeeLeaveQuotaHistory::insert($employeeLeaveQuotaHistories);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_quota_histories');
    }

};
