<?php

use App\Models\Company;
use App\Scopes\ActiveScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AttendanceSetting;
use App\Models\EmployeeShift;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = Company::withoutGlobalScope(ActiveScope::class)->get();

        foreach ($companies as $company) {
            $attendanceSetting = $company->attendanceSetting;

            if ($attendanceSetting) {
                EmployeeShift::withoutGlobalScope(ActiveScope::class)
                    ->where('company_id', $company->id)
                    ->where('id', $attendanceSetting->default_employee_shift)
                    ->update(['halfday_mark_time' => '13:00:00']);
            }
            else {

                $employeeShift = EmployeeShift::withoutGlobalScope(ActiveScope::class)
                    ->where('company_id', $company->id)
                    ->first();

                if ($employeeShift) {
                    EmployeeShift::withoutGlobalScope(ActiveScope::class)
                        ->where('company_id', $company->id)
                        ->whereNull('halfday_mark_time')
                        ->update(['halfday_mark_time' => '13:00:00']);
                }
            }
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
