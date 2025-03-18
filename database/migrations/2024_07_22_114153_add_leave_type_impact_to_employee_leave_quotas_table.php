<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->boolean('leave_type_impact')->default(0)->after('carry_forward_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->dropColumn('leave_type_impact');
        });
    }

};
