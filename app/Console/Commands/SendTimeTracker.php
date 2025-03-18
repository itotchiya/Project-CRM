<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\User;
use App\Models\Company;
use App\Models\ProjectTimeLog;
use App\Events\TimeTrackerReminderEvent;
use Carbon\Carbon;

class SendTimeTracker extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-time-tracker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send time tracker';

    /**
     *
     */

    public function handle()
    {

        $companies = Company::active()->select(['companies.id as id', 'timezone', 'time'])
            ->join('log_time_for', 'log_time_for.company_id', '=', 'companies.id')
            ->where('tracker_reminder', 1)
            ->get();

        if ($companies->isEmpty()) {
            $this->error('No Company with tracker_reminder enabled');

            return Command::SUCCESS;
        }

        $currentDay = now()->format('Y-m-d');

        foreach ($companies as $company) {

            $startDateTime = Carbon::parse($currentDay . ' ' . $company->time);
            $currentDateTime = now()->timezone($company->timezone);

            if ($currentDateTime->format('H:i') == $startDateTime->format('H:i')) {

                // Check if there's a holiday for the current day and company
                $holiday = Holiday::where('company_id', $company->id)
                    ->where('date', $currentDay)
                    ->exists();

                if ($holiday) {
                    continue;
                }

                $employeeIds = User::allEmployees(null, false, null, $company->id)->pluck('id');

                $employeeIds->each(function ($employeeId) use ($currentDay) {
                    $leaveExists = Leave::where('leave_date', $currentDay)
                        ->where('status', 'approved')
                        ->where('user_id', $employeeId)
                        ->exists();

                    $timeLogExists = ProjectTimeLog::whereDate('start_time', $currentDay)
                        ->where('user_id', $employeeId)
                        ->exists();

                    $user = User::find($employeeId);

                    if (!$leaveExists && !$timeLogExists && $user && $user->email_notifications) {
                        event(new TimeTrackerReminderEvent($user));
                    }
                });
            }
        }

        return Command::SUCCESS;
    }

}
