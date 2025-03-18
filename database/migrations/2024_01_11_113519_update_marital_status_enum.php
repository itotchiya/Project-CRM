<?php

use App\Enums\MaritalStatus;
use App\Models\EmployeeDetails;
use App\Models\LeaveType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->string('marital_status')->default(MaritalStatus::Single->value)->change();
        });

        EmployeeDetails::withoutGlobalScopes()->where('marital_status', 'unmarried')->update(['marital_status' => MaritalStatus::Single]);

        $leaveTypes = LeaveType::withoutGlobalScopes()->get();

        foreach ($leaveTypes as $leaveType) {
            $maritalStatus = json_decode($leaveType->marital_status);

            if (is_array($maritalStatus)) {
                $maritalStatus = array_map(function ($status) {
                    return $status === 'unmarried' ? MaritalStatus::Single->value : $status;
                }, $maritalStatus);

                $leaveType->marital_status = json_encode($maritalStatus);
                $leaveType->save();
            }
        }
    }

};
