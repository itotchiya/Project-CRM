<?php

use App\Models\Company;
use App\Models\DashboardWidget;
use App\Models\EmailNotificationSetting;
use App\Models\LeaveSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->enum('manager_status_permission', ['pre-approve', 'approved'])->nullable();
        });

        Schema::create('leave_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('manager_permission', ['pre-approve', 'approved', 'cannot-approve'])->default('pre-approve');
            $table->timestamps();
        });

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            $company->leaveSetting()->create();
            $this->workAnniversaryDashboardWidget($company);
            $this->birthdayPaymentNotification($company);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_settings');
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('manager_status_permission');
        });
    }

    private function birthdayPaymentNotification($company)
    {
        EmailNotificationSetting::firstOrCreate([
            'setting_name' => 'Birthday notification',
            'company_id' => $company->id,
            'send_email' => 'yes',
            'send_slack' => 'yes',
            'send_push' => 'no',
            'slug' => str_slug('Birthday notification')
        ]);

        EmailNotificationSetting::firstOrCreate([
            'setting_name' => 'Payment Notification',
            'send_email' => 'yes',
            'company_id' => $company->id,
            'send_slack' => 'no',
            'send_push' => 'no',
            'slug' => 'payment-notification'
        ]);
    }

    private function workAnniversaryDashboardWidget($company)
    {
        $widget = new DashboardWidget();
        $widget->company_id = $company->id;
        $widget->widget_name = 'work_anniversary';
        $widget->status = 1;
        $widget->dashboard_type = 'private-dashboard';
        $widget->save();
    }

};
