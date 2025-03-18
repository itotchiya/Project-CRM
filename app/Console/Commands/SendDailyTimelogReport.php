<?php

namespace App\Console\Commands;

use App\Events\DailyTimeLogReportEvent;
use App\Models\Company;
use App\Models\LogTimeFor;
use App\Models\Role;
use Illuminate\Console\Command;

class SendDailyTimelogReport extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-daily-timelog-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily timelog report';

    public function handle()
    {
        Company::active()->select(['id', 'logo', 'company_name'])->chunk(50, function ($companies) {

            foreach ($companies as $company) {
                $timelogSetting = LogTimeFor::where('company_id', $company->id)->first();

                if ($timelogSetting->timelog_report !== 1) {
                    continue;
                }

                $roles = Role::with('users')
                    ->where('company_id', $company->id)
                    ->whereIn('id', json_decode($timelogSetting->daily_report_roles))
                    ->get();

                foreach ($roles as $role) {
                    foreach ($role->users as $user) {
                        event(new DailyTimeLogReportEvent($user, $role, $company));
                    }
                }
            }
        });

        return Command::SUCCESS;

    }

}
