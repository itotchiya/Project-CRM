<?php

use App\Models\Company;
use App\Models\DashboardWidget;
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
            $widget = [
                'widget_name' => 'headcount',
                'status' => 1,
                'company_id' => $company->id,
                'dashboard_type' => 'admin-hr-dashboard'
            ];

            DashboardWidget::create($widget);

            $widget = [
                'widget_name' => 'joining_vs_attrition',
                'status' => 1,
                'company_id' => $company->id,
                'dashboard_type' => 'admin-hr-dashboard'
            ];

            DashboardWidget::create($widget);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DashboardWidget::where('widget_name', 'headcount')->delete();
    }

};
