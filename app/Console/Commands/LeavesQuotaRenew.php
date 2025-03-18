<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class LeavesQuotaRenew extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:leaves-quota-renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew leaves quota for all employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Company::active()->chunk(50, function ($companies) {
            foreach ($companies as $company) {
                $this->renewLeavesQuotaForCompany($company);
            }
        });

    }

    /**
     * Renew leaves quota for the company
     */
    public function renewLeavesQuotaForCompany(Company $company)
    {

        if ($company->leaves_start_from == 'year_start') {
            $today = Carbon::now($company->timezone)->startOfDay();
            $companyYearStart = Carbon::create($today->year, (int)$company->year_starts_from, tz: $company->timezone)->startOfDay();

            if ($today->ne($companyYearStart)) {
                return;
            }
        }

        $employees = User::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->when($company->leaves_start_from == 'joining_date', function ($query) use ($company) {
                $query->whereHas('employeeDetail', function ($q) use ($company) {
                    $q->whereMonth('joining_date', Carbon::now($company->timezone)->month)
                        ->whereDay('joining_date', Carbon::now($company->timezone)->day);
                });
            })
            ->onlyEmployee()->get();

        foreach ($employees as $employee) {
            $this->renewLeavesQuota($employee);
        }
    }

    /**
     * Renew leaves quota for the employee
     */
    public function renewLeavesQuota(User $employee)
    {
        $employeeLeaveTypes = $employee->leaveTypes;

        foreach ($employeeLeaveTypes as $employeeLeaveType) {
            $leaveType = $employeeLeaveType->leaveType;
            $noOfLeaves = $leaveType->no_of_leaves;

            if ($leaveType->unused_leave == 'carry forward') {
                $noOfLeaves += $employeeLeaveType->leaves_remaining;
            }

            $this->info('Renewing leaves quota for ' . $employee->name . ' for ' . $leaveType->type_name . ' with ' . $noOfLeaves . ' leaves');

            $employeeLeaveType->update([
                'no_of_leaves' => $noOfLeaves,
                'leaves_remaining' => $noOfLeaves,
                'leaves_used' => 0
            ]);
        }
    }

}
