<?php

namespace Database\Seeders;

use App\Models\EmailNotificationSetting;
use Illuminate\Database\Seeder;

class EmailSettingSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $notificationSettings = [
            ['setting_name' => 'User Registration/Added by Admin', 'send_email' => 'yes', 'slug' => str_slug('User Registration/Added by Admin')],
            ['setting_name' => 'Employee Assign to Project', 'send_email' => 'yes', 'slug' => str_slug('Employee Assign to Project')],
            ['setting_name' => 'New Notice Published', 'send_email' => 'no', 'slug' => str_slug('New Notice Published')],
            ['setting_name' => 'User Assign to Task', 'send_email' => 'yes', 'slug' => str_slug('User Assign to Task')],
        ];

        EmailNotificationSetting::insert($notificationSettings);
    }

}
