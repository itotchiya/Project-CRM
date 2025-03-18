<?php

namespace App\Console\Commands;

use App\Http\Controllers\DatabaseBackupSettingController;
use App\Models\DatabaseBackupSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AutoDatabaseBackup extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-database-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto create database backup';

    /**
     * Execute the console command.
     *
     * @return bool Returns true if backup was created, false otherwise
     */
    public function handle(): bool
    {
        // Get the first record of the database backup setting
        $backupSetting = DatabaseBackupSetting::first();

        // If there's no record or if the status is inactive, return false
        if (!$backupSetting || $backupSetting->status == 'inactive') {
            $this->info('Database Settings is inactive');

            return Command::SUCCESS;
        }

        // Get the backups list
        $backups = (new DatabaseBackupSettingController())->getBackup();

        // Reverse the backups array to get the most recent backup
        $backups = array_reverse($backups);

        // If there's no backup, create one immediately
        if (count($backups) == 0) {
            Artisan::call('backup:run', ['--only-db' => true, '--disable-notifications' => true]);

            return Command::SUCCESS;
        }

        // Calculate the difference between the most recent backup and today's date
        $date = Carbon::parse(($backups)[0]['last_modified']);
        $dateDifference = $date->diffInDays(now());

        // If the difference is less than the backup_after_days setting, return false
        if ($dateDifference < $backupSetting->backup_after_days) {
            $this->info('Backup already created for today.');

            return Command::SUCCESS;
        }

        // Get the current time in the timezone set in the global setting
        $nowTimeWithTimeZone = now()->setTimezone(global_setting()->timezone)->format('H:i:s');
        $settingHourOfDay = Carbon::createFromFormat('H:i:s', $backupSetting->hour_of_day)->format('H:i:s');


        // If the current time is equal or greater than the hour_of_day setting, create a backup
        if ($nowTimeWithTimeZone >= $settingHourOfDay) {
            $this->info('Backup created successfully.');
            Artisan::call('backup:run', ['--only-db' => true, '--disable-notifications' => true]);

            return Command::SUCCESS;
        }

        return Command::SUCCESS;
    }

}
