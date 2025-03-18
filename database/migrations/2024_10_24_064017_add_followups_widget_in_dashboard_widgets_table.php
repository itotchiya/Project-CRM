<?php

use App\Models\Company;
use App\Models\DashboardWidget;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $companies = Company::all();

        foreach ($companies as $company) {
            $widget = [
            'widget_name' => 'follow_ups',
            'status' => 1,
            'dashboard_type' => 'private-dashboard',
            'company_id' => $company->id
            ];

            DashboardWidget::create($widget);
        }

        $employees = \App\Models\EmployeeDetails::all();

        foreach ($employees as $employee) {
            $employee->calendar_view = 'task,events,holiday,tickets,leaves,follow_ups';
            $employee->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DashboardWidget::where('widget_name', 'follow_ups')->delete();
    }

};
