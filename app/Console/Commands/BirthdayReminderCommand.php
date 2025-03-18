<?php

namespace App\Console\Commands;

use App\Events\BirthdayReminderEvent;
use App\Models\Company;
use App\Models\EmployeeDetails;
use Illuminate\Console\Command;

class BirthdayReminderCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send birthday notification to everyone';

    /**
     * Handle the command.
     *
     * This method retrieves a list of companies, and for each company it retrieves
     * all employees with an upcoming birthday and triggers the "BirthdayReminderEvent".
     */
    public function handle()
    {
        // Get the current day in the format of "m-d"
        $currentDay = now()->format('m-d');

        // Retrieve all companies
        Company::active()->select('id')->chunk(50, function ($companies) use ($currentDay) {
            // Loop through each company
            foreach ($companies as $company) {
                // Retrieve all active employees with an upcoming birthday for the current company
                $upcomingBirthday = EmployeeDetails::join('users', 'employee_details.user_id', '=', 'users.id')
                    ->where('employee_details.company_id', $company->id)
                    ->where('users.status', 'active')
                    ->whereRaw('DATE_FORMAT(`date_of_birth`, "%m-%d") = "' . $currentDay . '"')
                    ->orderBy('employee_details.date_of_birth')
                    ->select('employee_details.company_id', 'employee_details.date_of_birth', 'users.name', 'users.image', 'users.id')
                    ->get()
                    ->toArray();

                // If there is any employee with an upcoming birthday, trigger the "BirthdayReminderEvent"
                if ($upcomingBirthday != null) {
                    event(new BirthdayReminderEvent($company, $upcomingBirthday));
                }
            }
        });

        return Command::SUCCESS;
    }

}
