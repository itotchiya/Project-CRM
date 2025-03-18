<?php

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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->enum('leavetype', ['monthly', 'yearly'])->nullable();
        });

        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->text('carry_forward_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('leavetype');
        });

        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->dropColumn('carry_forward_status');
        });
    }
};
