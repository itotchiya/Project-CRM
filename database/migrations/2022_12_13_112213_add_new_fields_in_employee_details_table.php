<?php

use App\Models\Company;
use App\Models\DashboardWidget;
use Illuminate\Support\Facades\Schema;
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
        Schema::table('employee_details', function (Blueprint $table) {

            $table->date('probation_end_date')->nullable()->after('reporting_to');
            $table->date('notice_period_start_date')->nullable()->after('reporting_to');
            $table->date('notice_period_end_date')->nullable()->after('reporting_to');
            $table->string('marital_status')->nullable()->after('reporting_to');
            $table->date('marriage_anniversary_date')->nullable()->after('reporting_to');
            $table->string('employment_type')->nullable()->after('reporting_to');
            $table->date('internship_end_date')->nullable()->after('reporting_to');
            $table->date('contract_end_date')->nullable()->after('reporting_to');

        });

        $companies = Company::select('id')->get();


        foreach ($companies as $company) {
            $widget = [
                [
                    'widget_name' => 'notice_period_duration',
                    'status' => 1,
                    'company_id' => $company->id,
                    'dashboard_type' => 'private-dashboard'
                ],
                [
                    'widget_name' => 'probation_date',
                    'status' => 1,
                    'company_id' => $company->id,
                    'dashboard_type' => 'private-dashboard'
                ], [
                    'widget_name' => 'contract_date',
                    'status' => 1,
                    'company_id' => $company->id,
                    'dashboard_type' => 'private-dashboard'
                ],
                [
                    'widget_name' => 'internship_date',
                    'status' => 1,
                    'company_id' => $company->id,
                    'dashboard_type' => 'private-dashboard'
                ]
            ];

            DashboardWidget::insert($widget);
        }

        // Remove duplicates from module_settings table
        \Illuminate\Support\Facades\DB::statement('DELETE t1 FROM module_settings t1
        INNER JOIN module_settings t2 WHERE
        t1.id > t2.id
        AND t1.type = t2.type
        AND t1.module_name = t2.module_name
        AND t1.company_id = t2.company_id;');

        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->boolean('monthly_report')->default(0);
            $table->string('monthly_report_roles')->nullable();
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->after('leave_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn('probation_end_date');
            $table->dropColumn('notice_period_start_date');
            $table->dropColumn('notice_period_end_date');
            $table->dropColumn('marital_status');
            $table->dropColumn('marriage_anniversary_date');
            $table->dropColumn('employment_type');
            $table->dropColumn('internship_end_date');
            $table->dropColumn('contract_end_date');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('unique_id');
        });
    }

};
