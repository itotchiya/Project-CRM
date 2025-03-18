<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\EmployeeLeaveQuota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AnnualCarryForwardLeaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:annual-carry-forward-leaves {company?} {user?} {leaveType?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carry forward last year leaves to next year.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyID = $this->argument('company');
        $userID = $this->argument('user');
        $leaveTypeID = $this->argument('leaveType');
        // dd($companyID, $userID, $leaveTypeID);
        
        $companies = Company::active()->with(['leaveTypes' => function ($query) use ($leaveTypeID) {
            if ($leaveTypeID != '') {
                return $query->where('id', $leaveTypeID);
            }

            return $query;
        }]);

        if ($companyID != '') {
            $companies = $companies->where('id', $companyID);
        }

        $companies->chunk(50, function ($companies) use ($userID) {
            foreach ($companies as $company) {

                $leaveTypes = $company->leaveTypes;

                $settings = $company;

                $users = User::withoutGlobalScopes()->whereHas('employeeDetail')
                    ->with(['leaves', 'leaveTypes', 'leaveTypes.leaveType', 'employeeDetail'])
                    ->where('company_id', $company->id);

                if ($userID != '') {
                    $users = $users->where('id', $userID);
                }

                $users = $users->get();

                foreach ($users as $user) {


                    if ($settings && $settings->leaves_start_from == 'joining_date') {
                        $currentYearJoiningDate = Carbon::parse($user->employeeDetail->joining_date->format((now($settings->timezone)->year) . '-m-d'));;

                        if ($currentYearJoiningDate->copy()->format('Y-m-d') != now($settings->timezone)->format('Y-m-d')) {
                            continue;
                        }

                        if ($currentYearJoiningDate->gt($user->employeeDetail->joining_date)) {
                            $differenceMonth = $currentYearJoiningDate->startOfMonth()->diffInMonths($user->employeeDetail->joining_date->copy()->startOfMonth());
            
                        } else {
                            continue;
                        }

                        if ($differenceMonth > 12) {
                            continue;
                        }
            
                    } else {
                        // yearly setting year_start
            
                        $yearFrom = $settings && $settings->year_starts_from ? $settings->year_starts_from : 1;
                        $startingDate = Carbon::create(now()->year, $yearFrom)->startOfMonth();

                        if ($startingDate->copy()->format('Y-m-d') != now()->format('Y-m-d')) {
                            continue;
                        }

                    }

                    foreach ($leaveTypes as $value) {
                        $leaveQuota = EmployeeLeaveQuota::where('user_id', $user->id)->where('leave_type_id', $value->id)->first();
               
                        if ($leaveQuota && $value->unused_leave == 'carry forward' && $leaveQuota->carry_forward_applied == 0) {
                            $leaveQuota->carry_forward_leaves = $leaveQuota->leaves_remaining;
                            $leaveQuota->overutilised_leaves = 0;
                            $leaveQuota->carry_forward_applied = 1;
                            $leaveQuota->save();
                        }

                    }
                }
            }
        });
    }

}
