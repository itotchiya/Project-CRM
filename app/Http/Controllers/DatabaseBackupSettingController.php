<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\DatabaseBackup\UpdateRequest;
use App\Models\DatabaseBackupSetting;
use App\Models\GlobalSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class DatabaseBackupSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.databaseBackupSetting');
        $this->activeSettingMenu = 'database_backup_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()));

            return $next($request);
        });
    }

    public function index()
    {
        $backups = $this->getBackup();

        $this->backupSetting = DatabaseBackupSetting::first();
        $this->globalSetting = GlobalSetting::first();
        $this->backups = array_reverse($backups);

        return view('database-backup-settings.index', $this->data);
    }

    public function getBackup()
    {
        $disk = Storage::disk('localBackup');
        try {

            $files = $disk->files('/backup');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        $backups = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.zip') && $disk->exists($file)) {
                $backups[] = [
                    'file_path' => $file,
                    'file_name' => str_replace(config('laravel-backup.backup.name') . 'backup/', '', $file),
                    'file_size' => $disk->size($file),
                    'last_modified' => $disk->lastModified($file),
                ];
            }
        }

        return $backups;
    }

    public function create()
    {
        $this->backupSetting = DatabaseBackupSetting::first();

        return view('database-backup-settings.settings', $this->data);
    }

    public function store(UpdateRequest $request)
    {
        $backupSetting = DatabaseBackupSetting::first();
        $backupSetting->status = isset($request->status) ? 'active' : 'inactive';
        $backupSetting->hour_of_day = Carbon::createFromFormat($this->company->time_format, $request->hour_of_day)->format('H:i:s');
        $backupSetting->backup_after_days = $request->backup_after_days;
        $backupSetting->delete_backup_after_days = $request->delete_backup_after_days;
        $backupSetting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function createBackup()
    {

        try {
            config(['queue.default' => 'database']);
            /* Only database backup */
            Artisan::queue('backup:run', ['--only-db' => true, '--disable-notifications' => true]);
            sleep(3);

            return Reply::success(__('messages.databasebackup.backedupSuccessful'));
        } catch (Exception $e) {
            return Reply::error(__('messages.databasebackup.databaseError') . ' =>' . $e->getMessage());
        }
    }

    public function download($file_name)
    {
        $file = config('laravel-backup.backup.name') . '/backup/' . $file_name;
        $disk = Storage::disk('localBackup');

        if (!$disk->exists($file)) {
            return Reply::error(__('messages.databasebackup.backupNotExist'));
        }

        $fs = Storage::disk('localBackup')->getDriver();
        $stream = $fs->readStream($file);

        return \Response::stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-disposition' => 'attachment; filename="' . basename($file) . '"',
        ]);
    }

    public function delete($file_name)
    {
        $disk = Storage::disk('localBackup');

        if ($disk->exists(config('laravel-backup.backup.name') . '/backup/' . $file_name)) {
            $disk->delete(config('laravel-backup.backup.name') . '/backup/' . $file_name);

            // For showing number of backed-up databases
            $files = $disk->files('/backup');

            return Reply::successWithData(__('messages.databasebackup.backupDeleted'), ['fileCount' => count($files)]);

        }

        return Reply::error(__('messages.databasebackup.backupNotExist'));
    }

    public static function humanFileSize($size, $unit = '')
    {
        if ((!$unit && $size >= 1 << 30) || $unit == 'GB') {
            return number_format($size / (1 << 30), 2) . 'GB';
        }

        if ((!$unit && $size >= 1 << 20) || $unit == 'MB') {
            return number_format($size / (1 << 20), 2) . 'MB';
        }

        if ((!$unit && $size >= 1 << 10) || $unit == 'KB') {
            return number_format($size / (1 << 10), 2) . 'KB';
        }

        return number_format($size) . ' bytes';
    }

}
