<?php

use App\Models\Company;
use App\Models\EmailNotificationSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [];
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $settings[] = [
                'send_email' => 'no',
                'send_push' => 'no',
                'company_id' => $company->id,
                'send_slack' => 'no',
                'setting_name' => 'Daily Schedule Notification',
                'slug' => 'daily-schedule-notification',
            ];
        }

        EmailNotificationSetting::insert($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            EmailNotificationSetting::where('slug', 'daily-schedule-notification')->delete();
        });
    }

};


