<?php

namespace App\Console\Commands;

use App\Events\TaskReminderEvent;
use App\Models\Company;
use App\Models\Task;
use App\Models\TaskboardColumn;
use Illuminate\Console\Command;

class SendTaskReminder extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-task-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send task reminders';

    /**
     *
     */
    public function handle()
    {
        // Get all companies

        Company::active()->select(['id', 'timezone', 'before_days', 'after_days', 'on_deadline'])->chunk(50, function ($companies) {
            // Loop through each company
            foreach ($companies as $company) {
                // Get current time in company's timezone
                $now = now($company->timezone);

                // If the company has set "before_days"
                if ($company->before_days > 0) {
                    $beforeDeadline = $now->clone()->subDays($company->before_days)->format('Y-m-d');
                    $this->sendReminders($beforeDeadline, $company);
                }

                // If the company has set "after_days"
                if ($company->after_days > 0) {
                    $afterDeadline = $now->clone()->addDays($company->after_days)->format('Y-m-d');
                    $this->sendReminders($afterDeadline, $company);
                }

                // If the company has set "on_deadline"
                if ($company->on_deadline) {
                    $onDeadline = $now->clone()->format('Y-m-d');
                    $this->sendReminders($onDeadline, $company);
                }
            }
        });

        return Command::SUCCESS;
    }

    /**
     * Send task reminders for the given date and company.
     *
     * @param string $dueDate
     * @param Company $company
     *
     * @return void
     */
    private function sendReminders(string $dueDate, Company $company)
    {
        // Get the "completed" taskboard column for the company
        $completedTaskColumn = TaskboardColumn::where('company_id', $company->id)
            ->where('slug', 'completed')
            ->first();

        // Get all tasks for the given date and company that are not in the "completed" column
        $tasks = Task::where('due_date', $dueDate)
            ->where('company_id', $company->id)
            ->where('board_column_id', '<>', $completedTaskColumn->id)
            ->get();

        // Loop through each task
        foreach ($tasks as $task) {
            // Fire a TaskReminderEvent for the task
            event(new TaskReminderEvent($task));
        }
    }

}
