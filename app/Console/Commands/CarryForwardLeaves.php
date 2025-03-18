<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveType;
use App\Models\Company;
use Illuminate\Support\Carbon;

use function Symfony\Component\VarDumper\Dumper\esc;

class CarryForwardLeaves extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carry-forward-leave';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update carry forward leaves';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        Company::active()->chunk(50, function ($companies) {

            foreach ($companies as $company) {

                $this->getStartMonthAndYear($company);
            }
        });

        return Command::SUCCESS;
    }

    public function getStartMonthAndYear(Company $company){
        // Fetch all users 

        $users = User::withoutGlobalScopes()->onlyEmployee()->with(['leaves', 'leaveTypes', 'leaveTypes.leaveType', 'employeeDetail'])
                        ->where('company_id',$company->id)->get();

        foreach ($users as $user) {

            if ($company->leaves_start_from == 'year_start') {

                $today = Carbon::now($company->timezone)->startOfDay();
                $startMonth = Carbon::create($today->year, (int)$company->year_starts_from, tz: $company->timezone)->startOfDay();
                $startYear = now()->year;
                

            }else{
                
                $joiningYear = $user->employeeDetail->joining_date->format('Y');
               
                    if($joiningYear < now()->year){
                        $startMonth = Carbon::create(now()->year, 1, 1, 0, 0, 0, $company->timezone);
                        $startYear = now()->year;

                        

                    }else{
                        $startMonth = $user->employeeDetail->joining_date;
                        $startYear = $user->employeeDetail->joining_date->format('Y');
                       
                    }
            }

         
      
            // Loop through each month from the starting month until the current month
            for ($month = $startMonth->copy(); $month->month  <= now()->startOfMonth()->month; $month->addMonth()) {
                
               
                // skip the if user has not joined yet or joined this month
                if ($user->employeeDetail->joining_date->year == $startYear && $user->employeeDetail->joining_date->month >= $month->month) {
                   
                    continue;
                }
                    
                $this->carryForwardLeave($user, $month, $startYear, $company);
                
            }
        }
    }

    private function carryForwardLeave($user, $month, $startYear, Company $company){
       

        $leaveTypes = LeaveType::withTrashed()->where('unused_leave', 'carry forward')->where('company_id',$company->id)->get();

        foreach($leaveTypes as $leaveType){

            // skip if leave is not creted yet
            $createdAt = $leaveType->created_at;

            if (
                $createdAt != null &&
                $createdAt->year == (string)$startYear &&
                ($createdAt->month >= (string)$month->month)
                    ) {
                    continue;
            }

            $employeeLeaveQuota = EmployeeLeaveQuota::where('leave_type_id', $leaveType->id)->where('user_id', $user->id)
                ->where('carry_forward_status', 'like', '%'.$month->format('F Y').'%')->exists();
            
            if (!$employeeLeaveQuota) {
                // false and false then true
                $leaveCountFrom = $company->leaves_start_from; // joining_date or year_start
                $fromMonth = $company->year_starts_from; // month no 1 to 12
                $joiningDate = Carbon::parse($user->employeeDetail->joining_date);
                

                // Get Approved Leave's count for user
                if($leaveType->leavetype == 'yearly'){
                    
                   if (
                        ($leaveCountFrom == 'joining_date' && $user->employeeDetail->joining_date->month != $month->month) ||
                        ($leaveCountFrom == 'year_start' && $fromMonth != $month->month)
                    ) {
                        continue;
                    }

                   if ($leaveCountFrom == 'joining_date') {
                        $leavePeriodStart = $joiningDate;
                        $leavePeriodEnd = $joiningDate->copy()->addYear();
                
                        // Example for second period: start from 1st August 2025 to 1st August 2026
                        $now = Carbon::now();
                        while ($leavePeriodEnd->lessThan($now)) {
                            $leavePeriodStart = $leavePeriodStart->copy()->addYear();
                            $leavePeriodEnd = $leavePeriodEnd->copy()->addYear();
                        }

                        $approvedLeaves = $user->leaves()
                            ->where('leave_type_id', $leaveType->id)
                            ->where('status', 'approved')
                            ->whereBetween('leave_date', [$leavePeriodStart, $leavePeriodEnd])
                            ->count();
                            // info('joining_date-'.$user->id);
                            // info([$approvedLeaves,$leavePeriodStart, $leavePeriodEnd]);
                    } elseif ($leaveCountFrom == 'year_start') {
                        $yearStart = Carbon::createFromDate($startYear, $fromMonth, 1);
                        $leavePeriodStart = $joiningDate->greaterThan($yearStart) ? $joiningDate : $yearStart;
                        $leavePeriodEnd = $yearStart->copy()->addYear();
                        
                        
                        // Example for first period: start from joining date to next year start
                        if ($leavePeriodStart->greaterThan($yearStart)) {
                            $leavePeriodEnd = $leavePeriodStart->copy()->addMonths(12);
                        }
                
                        $approvedLeaves = $user->leaves()
                            ->where('leave_type_id', $leaveType->id)
                            ->where('status', 'approved')
                            ->whereBetween('leave_date', [$leavePeriodStart, $leavePeriodEnd])
                            ->count();
                            // info('year_start-'.$user->id);
                            // info([$approvedLeaves,$leavePeriodStart, $leavePeriodEnd]);
                    }

            

                }else{

                    $approvedLeaves = $user->leaves()
                        ->whereYear('leave_date', $startYear)
                        ->whereMonth('leave_date', $month)
                        ->where('leave_type_id', $leaveType->id)
                        ->where('status', 'approved')
                        ->count();
                        // info('monthly-'.$user->id);
                        // info([$approvedLeaves,$startYear, $month]);
                }

                $leaveQuota = EmployeeLeaveQuota::where('user_id', $user->id)->where('leave_type_id', $leaveType->id)->first();
                
                if($leaveQuota){

                    // $totalRemainingLeaves = ($leaveQuota->leave_type_impact == 1) 
                    // ? $leaveQuota->no_of_leaves - $approvedLeaves
                    // : $leaveType->no_of_leaves - $approvedLeaves;

                    // $totalRemainingLeaves = $leaveType->leaveQuota - $approvedLeaves;

                    // if($totalRemainingLeaves > 0){

                        $carryForwardStatus = json_decode($leaveQuota->carry_forward_status, true) ?? [];
                        $carryForwardStatus[$month->format('F Y')] = true;

                        if($leaveType->deleted_at != null){

                            $leaveQuota->carry_forward_status = json_encode($carryForwardStatus);
                            $leaveQuota->save();

                        }else{
                            
                            $totalNOL = $leaveQuota->leaves_remaining + $leaveType->no_of_leaves;
                            
                            // $leaveQuota->no_of_leaves = $leaveQuota->no_of_leaves + $leaveType->no_of_leaves;
                            // $leaveQuota->leaves_remaining = $leaveQuota->no_of_leaves - $leaveQuota->leaves_used;
                            $leaveQuota->no_of_leaves = $totalNOL;
                            $leaveQuota->leaves_used = $approvedLeaves;
                            $leaveQuota->leaves_remaining = $totalNOL - $approvedLeaves;
                            $leaveQuota->carry_forward_status = json_encode($carryForwardStatus);
                            $leaveQuota->save();
                        }

                        $this->info('Carry forward leaves updated successfully.');
                    // }
                }
            }
        }
    }

}
