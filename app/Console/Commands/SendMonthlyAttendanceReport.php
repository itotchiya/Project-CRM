<?php

namespace App\Console\Commands;

use App\Events\MonthlyAttendanceEvent;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Console\Command;

class SendMonthlyAttendanceReport extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-monthly-attendance-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly attendance report';

    public function handle()
    {

        Company::active()
            ->select('companies.id as id', 'logo', 'company_name', 'monthly_report_roles')
            ->join('attendance_settings', 'attendance_settings.company_id', '=', 'companies.id')
            ->where('monthly_report', 1)->chunk(50, function ($companies) {
                foreach ($companies as $company) {

                    $roles = Role::with('users')
                    ->whereIn('id', json_decode($company->monthly_report_roles))
                    ->get();

                    $uniqueUsers = collect();

                    // Collect unique users based on email or ID
                    foreach ($roles as $role) {
                        foreach ($role->users as $user) {
                            $uniqueUsers[$user->id] = $user; // Use ID to prevent duplicates
                        }
                    }

                    // Now send emails only once per user
                    foreach ($uniqueUsers as $user) {
                        $this->info('Email sent: ' . $user->email);
                        event(new MonthlyAttendanceEvent($user, $company));
                    }

                }
            });

        return Command::SUCCESS;

    }

}
