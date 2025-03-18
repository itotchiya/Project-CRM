<?php

namespace App\Console\Commands;

use App\Events\AutoFollowUpReminderEvent;
use App\Models\Company;
use App\Models\DealFollowUp;
use Illuminate\Console\Command;

class SendAutoFollowUpReminder extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-auto-followup-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification of followup to employee or added by user';


    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        Company::active()->chunk(50, function ($companies) {
            foreach ($companies as $company) {
                $this->sendFollowUpReminder($company);
            }
        });

        return Command::SUCCESS;
    }

    public function sendFollowUpReminder($company)
    {
        $followups = DealFollowUp::with('lead', 'lead.leadAgent', 'lead.leadAgent.user')
            ->where('next_follow_up_date', '>=', now($company->timezone))
            ->whereHas('lead', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->where('send_reminder', 'yes')
            ->get();

        foreach ($followups as $followup) {

            $remindTime = $followup->remind_time;
            $reminderDate = null;

            if ($followup->remind_type == 'day') {
                $reminderDate = $followup->next_follow_up_date->subDays($remindTime);
            }
            elseif ($followup->remind_type == 'hour') {
                $reminderDate = $followup->next_follow_up_date->subHours($remindTime);
            }
            else {
                $reminderDate = $followup->next_follow_up_date->subMinutes($remindTime);
            }

            if ($reminderDate->format('Y-m-d H:i') == now($company->timezone)->format('Y-m-d H:i')) {
                event(new AutoFollowUpReminderEvent($followup, false));
            }

        }

    }

}


