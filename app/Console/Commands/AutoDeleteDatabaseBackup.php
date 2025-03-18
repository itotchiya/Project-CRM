<?php

namespace App\Console\Commands;

use App\Models\DatabaseBackupSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AutoDeleteDatabaseBackup extends Command
{

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'delete-database-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-delete database backups';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Fetch the first backup setting
        $backupSetting = DatabaseBackupSetting::first();

        // Check if the backup setting exists and its status is active
        if ($backupSetting && $backupSetting->status === 'active') {
            // Get the local backup disk
            $disk = Storage::disk('localBackup');
            // Get all files in the backup folder
            $files = $disk->files('/backup');

            // Loop through each file
            foreach ($files as $file) {
                // Check if the file is a .zip file and exists on the disk
                if (str_ends_with($file, '.zip') && $disk->exists($file)) {
                    // Parse the date the file was last modified
                    $date = Carbon::parse($disk->lastModified($file));
                    // Get the current date and time
                    $now = now();
                    // Calculate the difference between the modified date and the current date
                    $dateDifference = $date->diffInDays($now);

                    // Check if the difference is greater than the delete backup after days setting
                    if ((int)$backupSetting->delete_backup_after_days > 0 && $dateDifference >= (int)$backupSetting->delete_backup_after_days) {
                        // Delete the file
                        $disk->delete('backup/' . str_replace(config('laravel-backup.backup.name') . 'backup/', '', $file));
                    }
                }
            }
        }

        return Command::SUCCESS;
    }

}

