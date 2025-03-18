<?php

use App\Models\Company;
use Illuminate\Support\Facades\Schema;
use App\Models\EmailNotificationSetting;
use App\Models\EmployeeShift;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $settings = [
                [
                    'send_email' => 'no',
                    'send_push' => 'no',
                    'company_id' => $company->id,
                    'send_slack' => 'no',
                    'setting_name' => 'Clock In Notification',
                    'slug' => 'clock-in-notification',
                ],
                [
                    'send_email' => 'no',
                    'send_push' => 'no',
                    'company_id' => $company->id,
                    'send_slack' => 'no',
                    'setting_name' => 'Holiday Notification',
                    'slug' => 'holiday-notification',
                ],
                [
                    'send_email' => 'yes',
                    'send_push' => 'no',
                    'company_id' => $company->id,
                    'send_slack' => 'no',
                    'setting_name' => 'Estimate Notification',
                    'slug' => 'estimate-notification',
                ],
                [
                    'send_email' => 'yes',
                    'send_push' => 'no',
                    'company_id' => $company->id,
                    'send_slack' => 'no',
                    'setting_name' => 'Event Notification',
                    'slug' => 'event-notification',
                ],
                [
                    'send_email' => 'yes',
                    'send_push' => 'no',
                    'company_id' => $company->id,
                    'send_slack' => 'no',
                    'setting_name' => 'Message Notification',
                    'slug' => 'message-notification',
                ]
            ];

            EmailNotificationSetting::insert($settings);

        }

        Schema::table('lead_follow_up', function (Blueprint $table) {
            $table->string('status')->after('remind_type')->nullable();
        });


        foreach ($companies as $company) {
            EmployeeShift::create([
                'company_id' => $company->id,
                'shift_name' => 'Day Off',
                'shift_short_code' => 'DO',
                'late_mark_duration' => 0,
                'clockin_in_day' => 0
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_follow_up', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

};
