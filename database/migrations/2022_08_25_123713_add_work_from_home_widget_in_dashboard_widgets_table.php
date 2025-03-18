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
        Company::renameOrganisationTableToCompanyTable();

        if (Company::first()) {

            $widget = [
                'widget_name' => 'work_from_home',
                'status' => 1,
                'dashboard_type' => 'private-dashboard'
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
        DashboardWidget::where('widget_name', 'work_from_home')->delete();
    }

};
