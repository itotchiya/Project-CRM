<?php

use App\Models\Company;
use App\Models\EmailNotificationSetting;
use Illuminate\Database\Migrations\Migration;

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
                'setting_name' => 'Task Status Changed',
                'slug' => 'task-status-updated',
            ];
        }

        EmailNotificationSetting::insert($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        EmailNotificationSetting::where('slug', 'task-status-updated')->delete();
    }

};
