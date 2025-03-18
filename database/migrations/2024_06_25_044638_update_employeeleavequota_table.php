<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\EmployeeLeaveQuota;
use App\Models\Leave;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing records based on some conditions
        $quotaHistories = EmployeeLeaveQuota::all();

        foreach ($quotaHistories as $history) {

            $leavesCount = Leave::where('user_id', $history->user_id)->where('leave_type_id', $history->leave_type_id)->count();

            $history->update(['leaves_used' => $leavesCount]);
            $history->update(['leaves_remaining' => $history->no_of_leaves - $leavesCount]);

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
