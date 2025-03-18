<?php

use App\Models\Company;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_leave_quotas', function ($table){
            $table->double('overutilised_leaves')->default(0);
            $table->double('unused_leaves')->default(0);
            $table->double('carry_forward_leaves')->default(0);
            $table->double('carry_forward_applied')->default(0);
        });

        Schema::table('employee_leave_quota_histories', function ($table){
            $table->double('overutilised_leaves')->default(0);
            $table->double('unused_leaves')->default(0);
            $table->double('carry_forward_leaves')->default(0);
            $table->boolean('carry_forward_applied')->default(0);
        });

        Schema::table('leave_types', function ($table){
            $table->double('no_of_leaves')->change();
        });

        DB::table('employee_leave_quota_histories')->truncate();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_quota_histories', function ($table){
            $table->dropColumn(['carry_forward_applied']);
            $table->dropColumn(['carry_forward_leaves']);
            $table->dropColumn(['unused_leaves']);
            $table->dropColumn(['overutilised_leaves']);
        });

        Schema::table('employee_leave_quotas', function ($table){
            $table->dropColumn(['carry_forward_applied']);
            $table->dropColumn(['carry_forward_leaves']);
            $table->dropColumn(['unused_leaves']);
            $table->dropColumn(['overutilised_leaves']);
        });
    }

};
