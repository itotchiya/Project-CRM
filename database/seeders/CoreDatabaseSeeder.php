<?php

namespace Database\Seeders;

use App\Models\DatabaseBackupSetting;
use App\Models\GdprSetting;
use App\Models\LanguageSetting;
use App\Models\PusherSetting;
use App\Models\PushNotificationSetting;
use App\Models\SocialAuthSetting;
use App\Models\StorageSetting;
use App\Models\TranslateSetting;
use Illuminate\Database\Seeder;

class CoreDatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->dashboardBackupSetting();
        $this->fileStorageSetting();
        $this->gdprSetting();
        $this->languageSettings();
        $this->socialAuth();
        $this->appreciationIcon();
        TranslateSetting::create(['google_key' => null]);
        $this->pushNotification();
    }

    public function dashboardBackupSetting()
    {
        $backupSetting = new DatabaseBackupSetting();
        $backupSetting->status = 'inactive';
        $backupSetting->hour_of_day = '';
        $backupSetting->backup_after_days = '0';
        $backupSetting->delete_backup_after_days = '0';
        $backupSetting->save();
    }

    private function fileStorageSetting()
    {
        $storage = new StorageSetting();
        $storage->filesystem = 'local';
        $storage->status = 'enabled';
        $storage->save();
    }

    private function gdprSetting()
    {
        $gdpr = new GdprSetting();
        $gdpr->create();
    }

    private function languageSettings()
    {
        LanguageSetting::insert(LanguageSetting::LANGUAGES);
    }

    private function socialAuth()
    {
        SocialAuthSetting::create([
            'facebook_status' => 'disable',
            'google_status' => 'disable',
            'linkedin_status' => 'disable',
            'twitter_status' => 'disable',
        ]);
    }

    private function pushNotification()
    {
        $slack = new PushNotificationSetting();
        $slack->onesignal_app_id = null;
        $slack->onesignal_rest_api_key = null;
        $slack->notification_logo = null;
        $slack->save();

        $pusherSetting = new PusherSetting();
        $pusherSetting->save();

    }

    private function appreciationIcon()
    {
        $icons = [
            ['title' => 'Trophy', 'icon' => 'trophy'],
            ['title' => 'Thumbs Up', 'icon' => 'hand-thumbs-up'],
            ['title' => 'Award', 'icon' => 'award'],
            ['title' => 'Book', 'icon' => 'book'],
            ['title' => 'Gift', 'icon' => 'gift'],
            ['title' => 'Watch', 'icon' => 'watch'],
            ['title' => 'Cup', 'icon' => 'cup-hot'],
            ['title' => 'Puzzle', 'icon' => 'puzzle'],
            ['title' => 'Plane', 'icon' => 'airplane'],
            ['title' => 'Money', 'icon' => 'piggy-bank'],
        ];

        foreach ($icons as $icon) {
            \App\Models\AwardIcon::create($icon);
        }
    }

}

