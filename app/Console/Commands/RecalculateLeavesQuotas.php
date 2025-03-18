<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\EmployeeLeaveQuota;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculateLeavesQuotas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-leaves-quotas {company?} {user?} {leaveType?}';

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
        $companyID = $this->argument('company');
        $userID = $this->argument('user');
        $leaveTypeID = $this->argument('leaveType');
        // dd($companyID, $userID, $leaveTypeID);

        $companies = Company::active()
            ->select('id', 'company_name', 'timezone', 'leaves_start_from', 'year_starts_from')
            ->with(['leaveTypes' => function ($query) use ($leaveTypeID) {
            if ($leaveTypeID != '') {
                return $query->where('id', $leaveTypeID);
            }

            return $query;
        }]);

        if ($companyID != '') {
            $companies = $companies->where('id', $companyID);
        }

        // $this->info('Total Companies :: '. $companies->count());

        $companyCount = 0;

        $companies->chunk(10, function ($companies) use ($userID, &$companyCount) {
            foreach ($companies as $company) {

                $leaveTypes = $company->leaveTypes;

                $settings = $company;

                $users = User::withoutGlobalScopes()->whereHas('employeeDetail')
                    ->select('id', 'company_id', 'name')
                    ->with(['leaves', 'leaveTypes', 'leaveTypes.leaveType', 'employeeDetail'])
                    ->where('company_id', $company->id);

                if ($userID != '') {
                    $users = $users->where('id', $userID);
                }

                $users = $users->get();

                foreach ($users as $user) {

                    foreach ($leaveTypes as $value) {

                        $leaveQuota = EmployeeLeaveQuota::where('user_id', $user->id)->where('leave_type_id', $value->id)->first();
    
                        $joiningDate = $user->employeeDetail->joining_date->copy();

                        if (is_null($user->employeeDetail->joining_date)) {
                            continue;
                        }
                        
                        if (($leaveQuota && $leaveQuota->leave_type_impact == 1)) {
                            $noOfLeavesAlloted = $leaveQuota->no_of_leaves;

                        } else {
                            $noOfLeavesAlloted = $this->calculateNoOfLeavesAlloted($settings, $joiningDate, $user, $value);
                        }

                        if ($leaveQuota && $leaveQuota->carry_forward_leaves > 0) {
                            $noOfLeavesAlloted = $noOfLeavesAlloted + $leaveQuota->carry_forward_leaves;
                        }

                        $noOfLeavesTaken = $this->calculateNoOfLeavesTaken($settings, $joiningDate, $user, $value);


                        $noOfOverutilisedLeaves = $leaveQuota->overutilised_leaves ?? 0;
                        $noOfRemainingLeaves = ($noOfLeavesAlloted - $noOfLeavesTaken + $noOfOverutilisedLeaves);

                        if ($noOfRemainingLeaves < 0) {
                            $noOfOverutilisedLeaves = $noOfOverutilisedLeaves + abs($noOfRemainingLeaves);
                            $noOfRemainingLeaves = 0;
                        }


                        // $this->info($noOfOverutilisedLeaves .' '.($noOfLeavesTaken - $noOfOverutilisedLeaves));

                        if (($noOfOverutilisedLeaves > 0) && (($noOfLeavesTaken - $noOfOverutilisedLeaves) < $noOfLeavesAlloted)) {
                            $noOfRemainingLeaves = $noOfLeavesAlloted - ($noOfLeavesTaken - $noOfOverutilisedLeaves);
                        }
                
                        $noOfLeavesUnused = $leaveQuota->unused_leaves ?? 0;

                        if ($value->leavetype == 'monthly' && $value->unused_leave != 'carry forward') {
                            if ($noOfLeavesTaken > 0) {
                                $noOfOverutilisedLeaves = abs($noOfLeavesTaken - $noOfLeavesAlloted);
                            }

                            $noOfCurrentMonthLeaves = $this->calculateNoOfLeavesTakenCurrentMonth($settings, $user, $value);

                            if ($noOfCurrentMonthLeaves >= $value->no_of_leaves && $noOfLeavesTaken <= $noOfLeavesAlloted && $noOfLeavesTaken > 0) {
                                $noOfRemainingLeaves = 0;
                                $noOfOverutilisedLeaves = $noOfOverutilisedLeaves + ($noOfCurrentMonthLeaves - $value->no_of_leaves);

                            } else {
                                $noOfOverutilisedLeaves = $leaveQuota->overutilised_leaves ?? 0;

                                if ($noOfLeavesAlloted >= $value->no_of_leaves) {
                                    $noOfRemainingLeaves = $value->no_of_leaves - $noOfCurrentMonthLeaves;

                                } else {
                                    $noOfRemainingLeaves = $noOfLeavesAlloted - $noOfCurrentMonthLeaves;
                                }

                                if ($noOfRemainingLeaves < 0) {
                                    $noOfRemainingLeaves = 0;
                                }

                                $whole = floor($noOfRemainingLeaves);
                                $fraction = $noOfRemainingLeaves - $whole;

                                if ($fraction > 0 && $fraction <= 0.5) {
                                    $noOfRemainingLeaves = $whole + 0.5;

                                } else if ($fraction > 0.5 && $fraction <= 0.99) {
                                    $noOfRemainingLeaves = $whole + 1;
                                }
                            }

                            $noOfLeavesUnused = ($noOfLeavesAlloted - $noOfLeavesTaken - $noOfRemainingLeaves + $noOfOverutilisedLeaves);
                        }

                        // $this->info('Leaves Remaining :: '. $user->name.' ' .$value->type_name.' '. $noOfRemainingLeaves);
                        // $this->info('Leaves Overutilised :: '. $user->name.' ' .$value->type_name.' '. $noOfOverutilisedLeaves);
                        // $this->info('Leaves Unused :: '. $user->name.' ' .$value->type_name.' '. $noOfLeavesUnused);

                        $employeeLeaveQuota = EmployeeLeaveQuota::where('user_id', $user->id)
                        ->where('leave_type_id', $value->id)
                        ->first();

                        if (!$employeeLeaveQuota) {
                            EmployeeLeaveQuota::create([
                            'user_id' => $user->id,
                            'leave_type_id' => $value->id,
                            'no_of_leaves' => $noOfLeavesAlloted,
                            'leaves_used' => $noOfLeavesTaken,
                            'leaves_remaining' => $noOfRemainingLeaves,
                            'overutilised_leaves' => $noOfOverutilisedLeaves,
                            'unused_leaves' => $noOfLeavesUnused,
                            ]);

                        } else {
                            EmployeeLeaveQuota::where('user_id', $user->id)
                            ->where('leave_type_id', $value->id)
                            ->update(
                                [
                                    'no_of_leaves' => $noOfLeavesAlloted,
                                    'leaves_used' => $noOfLeavesTaken,
                                    'leaves_remaining' => $noOfRemainingLeaves,
                                    'overutilised_leaves' => $noOfOverutilisedLeaves,
                                    'unused_leaves' => $noOfLeavesUnused
                                ]
                            );
                        }
                    }

                // $this->info('Calculating Leaves Quota :: CompanyID = '.$user->company_id. ' , User = ' . $user->name . "\n");
                }

                $companyCount++;
                // $this->info('Calculating Leaves Quota :: Company# = '.$companyCount. "\n");
            }
        });

    }

    public function calculateNoOfLeavesAlloted($settings, $joiningDate, $user, $value)
    {
        $leaves = $value->no_of_leaves;
        $leaveToSubtract = 0;

        if ($settings && $settings->leaves_start_from == 'joining_date') {
            $currentYearJoiningDate = Carbon::parse($user->employeeDetail->joining_date->format((now($settings->timezone)->year) . '-m-d'));;

            if (now()->gt($currentYearJoiningDate)) {
                $differenceMonth = now()->startOfMonth()->diffInMonths($currentYearJoiningDate->copy()->startOfMonth());

            } else {
                $differenceMonth = now()->startOfMonth()->diffInMonths($currentYearJoiningDate->copy()->subYear()->startOfMonth());
            }

            $differenceMonth = $differenceMonth + 1; // Include current month also


            $countOfMonthsAllowed = $differenceMonth > 12 ? $differenceMonth - 12 : $differenceMonth;

            if ($user->employeeDetail->joining_date->year == now()->year && $value->leavetype == 'yearly') {            // Calculate remaining days after full months
                $remainingDays = now()->diffInDays($currentYearJoiningDate->copy()->subMonths($differenceMonth));
                $remainingDays += 2; // adding 2 for becaus same day and next day is not counting as diff


                if ($remainingDays >= 16) {
                    $countOfMonthsAllowed++;
                    $remainingDays = 0;
                }
            }

        } else {
            // yearly setting year_start

            $yearFrom = $settings && $settings->year_starts_from ? $settings->year_starts_from : 1;
            $startingDate = Carbon::create(now()->year, $yearFrom)->startOfMonth();

            $differenceMonth = ($user->employeeDetail->joining_date->year == now()->year) ? now()->startOfMonth()->diffInMonths($joiningDate->copy()->startOfMonth()) : now()->startOfMonth()->diffInMonths($startingDate);

            $differenceMonth = $differenceMonth + 1; // Include current month also

            $countOfMonthsAllowed = $differenceMonth > 12 ? $differenceMonth - 12 : $differenceMonth;

            $remainingDays = 0;
            $currentYearJoiningDate = Carbon::parse($user->employeeDetail->joining_date->format((now($settings->timezone)->year) . '-m-d'));;

            if ($user->employeeDetail->joining_date->year == now()->year && $value->leavetype == 'yearly') {
                $remainingDays = now()->diffInDays($currentYearJoiningDate->copy()->subMonths($differenceMonth));
                $remainingDays += 2; // adding 2 for becaus same day and next day is not counting as diff
    
                if ($remainingDays >= 16) {
                    $countOfMonthsAllowed++;
                    $remainingDays = 0;
                }
            }

        }

        if ($settings
        && $settings->leaves_start_from == 'year_start'
        && $joiningDate->gt(now($settings->timezone)->startOfyear()) // Check joining was less than 1 year
        ) {
            // give half leave for the month if joining date is after 15th of the month
            if ($value->leavetype == 'yearly') {
                $leaveToSubtract = ($joiningDate->diffInMonths($startingDate));
                $monthlyLeaves = ($value->no_of_leaves / 12);

                // Assign no of leaves according to date of joining if joined the same year
                if ($joiningDate->diffInMonths($startingDate) < 12) {
                    $leaveToSubtract = ($monthlyLeaves * $leaveToSubtract);
                }

                if ($joiningDate->day > 15) {
                    $leaveToSubtract = ($monthlyLeaves / 2) + ($leaveToSubtract);
                }

            } else {
                if ($joiningDate->day > 15) {
                    $leaveToSubtract = ($value->no_of_leaves / 2);
                }
            }

        }

        if ($settings
        && $settings->leaves_start_from == 'joining_date'
        && $joiningDate->day > 15
        && (now()->startOfMonth()->diffInMonths($user->employeeDetail->joining_date->startOfMonth()) < 12) // Check joining was less than 1 year
        ) {
            // give half leave for the month if joining date is after 15th of the month
            if ($value->leavetype == 'yearly') {
                $monthlyLeaves = ($value->no_of_leaves / 12);
                $leaveToSubtract = ($monthlyLeaves / 2);
                
            } else {
                $leaveToSubtract = ($value->no_of_leaves / 2);
            }

        }

        if ($value->leavetype == 'yearly') {
            $leaves = $value->no_of_leaves - $leaveToSubtract;
        
        } else if ($value->leavetype == 'monthly') {
            $leaves = ($value->no_of_leaves * $countOfMonthsAllowed) - $leaveToSubtract;

        }

        $noOfLeavesAlloted = number_format((float)$leaves, 2, '.', '');

        // Round off the fraction value for clear calculation and usage.
        // If fraction is between 0 to 0.5 then consider it as 0.5
        // If fraction is between 0.5 to 0.99 then consider it as 1

        $whole = floor($noOfLeavesAlloted);
        $fraction = $noOfLeavesAlloted - $whole;

        if ($fraction > 0 && $fraction <= 0.5) {
            $noOfLeavesAlloted = $whole + 0.5;

        } else if ($fraction > 0.5 && $fraction <= 0.99) {
            $noOfLeavesAlloted = $whole + 1;
        }


        // $this->info('Leaves Alloted :: '. $user->name.' ' .$value->type_name.' '. $noOfLeavesAlloted);

        return $noOfLeavesAlloted;
    }

    public function calculateNoOfLeavesTaken($settings, $joiningDate, $user, $value)
    {
        $currentYearJoiningDate = Carbon::parse($joiningDate->format((now($settings->timezone)->year) . '-m-d'));

        if ($currentYearJoiningDate->isFuture()) {
            $currentYearJoiningDate->subYear();
        }

        $leaveFrom = $currentYearJoiningDate->copy()->toDateString();
        $leaveTo = $currentYearJoiningDate->copy()->addYear()->toDateString();

        if ($settings->leaves_start_from !== 'joining_date') {
            $leaveStartYear = Carbon::parse(now()->format((now($settings->timezone)->year) . '-' . $settings->year_starts_from . '-01'));

            if ($leaveStartYear->isFuture()) {
                $leaveStartYear = $leaveStartYear->subYear();
            }

            $leaveFrom = $leaveStartYear->copy()->toDateString();
            $leaveTo = $leaveStartYear->copy()->addYear()->toDateString();
        }

        $fullDay = Leave::where('user_id', $user->id)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('leave_type_id', $value->id)
            ->where('duration', '<>', 'half day')
            ->count();

        $halfDay = Leave::where('user_id', $user->id)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('leave_type_id', $value->id)
            ->where('duration', 'half day')
            ->count();

        $noOfLeavesTaken = ($fullDay + ($halfDay / 2));

        // $this->info('Leaves Taken :: '. $user->name.' ' .$value->type_name.' '. $noOfLeavesTaken);

        return $noOfLeavesTaken;
    }

    public function calculateNoOfLeavesTakenCurrentMonth($settings, $user, $value)
    {

        $leaveFrom = now($settings->timezone)->startOfMonth()->toDateString();
        $leaveTo = now($settings->timezone)->endOfMonth()->toDateString();

        $fullDay = Leave::where('user_id', $user->id)
            ->whereBetween('created_at', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('leave_type_id', $value->id)
            ->where('duration', '<>', 'half day')
            ->count();

        $halfDay = Leave::where('user_id', $user->id)
            ->whereBetween('created_at', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('leave_type_id', $value->id)
            ->where('duration', 'half day')
            ->count();

        $noOfLeavesTaken = ($fullDay + ($halfDay / 2));

        // $this->info('Leaves Taken Current Month :: '. $user->name.' ' .$value->type_name.' '. $noOfLeavesTaken);

        return $noOfLeavesTaken;
    }

}
