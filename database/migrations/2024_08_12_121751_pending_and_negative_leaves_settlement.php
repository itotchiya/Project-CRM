<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Leave;
use App\Models\Company;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Company::active()->chunk(50, function ($companies) {
            foreach ($companies as $company) {
                $this->removeNegativeLeaves($company);
            }
        });
    }

    public function removeNegativeLeaves(Company $company)
    {
        try {
        // employee leave quota remove negative vales for the current 
            $employees = User::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->onlyEmployee()->get();
        
            foreach ($employees as $employee) {
               
                foreach ($employee->leaveTypes as $leaveQuota) {
                
                    $approvedLeavesCount = Leave::where('user_id', $employee->id)
                        ->where('leave_type_id', $leaveQuota->leave_type_id)
                        ->where('status', 'approved')
                        ->get()
                        ->sum(function($leave) {
                            return $leave->half_day_type ? 0.5 : 1;
                        });
    
                    
                    $leaveQuota->leaves_used = $approvedLeavesCount;
                    $leaveQuota->leaves_remaining = $leaveQuota->no_of_leaves - $approvedLeavesCount;
                    
                    if ($leaveQuota->leaves_used > $leaveQuota->no_of_leaves) {
                        
                        $leaveQuota->no_of_leaves = $leaveQuota->leaves_used;
                        $leaveQuota->leaves_remaining = 0;
                        
                    }
                    
                    $leaveQuota->save();
                        
                }

                foreach ($employee->leaveQuotaHistory as $history) {
                    // Determine the start and end dates based on for_month
                    $startDate = date('Y-m-01', strtotime($history->for_month));
                    $endDate = date('Y-m-t', strtotime($history->for_month));
                    
                    $count = Leave::where('leave_type_id', $history->leave_type_id)
                        ->where('status', 'approved')
                        ->where('user_id',$employee->id)
                        ->where(function ($query) use ($startDate, $endDate) {
                            $query->where('leave_date', '<=', $endDate)
                            ->where('leave_date', '>=', $startDate);
                        })->get()
                        ->sum(function($leave) {
                            return $leave->half_day_type ? 0.5 : 1;
                        });

                        $history->leaves_used = $count;
                        $history->leaves_remaining = $history->no_of_leaves - $count;
                        $history->updated_at = now(); // Update the timestamp if needed
                        $history->save(); // Save the changes
                }

            }

        } catch (\Exception $e) {
            // Log the error or handle it as needed
            Log::error('Error processing employee in company ID ' . $company->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // You may want to define reverse logic if applicable
    }
};
