<?php

namespace App\Console\Commands;

use App\Models\GlobalSetting;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;

class HideCronJobMessage extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hide-cron-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hide cron job message.';

    /**
     * Execute the console command.
     *`
     * @return mixed
     */

    public function handle()
    {

        $output = new ConsoleOutput();
        $output->writeln('<info>Cron Job seems to running and ran last at ' . now() . '</info>');


        $setting = GlobalSetting::first();
        $difference = !is_null($setting->last_cron_run) ? now()->diffInHours($setting->last_cron_run) : 0;

        // If difference between time is more than 12 hours or cron job is less than run the cron job
        // This is checked so that global cache do not reset every minute
        if ($difference > 12 || is_null($setting->last_cron_run)) {
            $setting->last_cron_run = now();
            // Update the last cron run time
            $setting->hide_cron_message = 1;

            // This will reset the global cache
            $setting->save();


        }

        return Command::SUCCESS;

    }

}
