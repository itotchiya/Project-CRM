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
        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->enum('shift_type', ['strict', 'flexible'])->default('strict')->after('shift_short_code');
            $table->float('flexible_half_day_hours')->after('shift_type');
            $table->float('flexible_total_hours')->after('shift_type');
            $table->float('flexible_auto_clockout')->after('flexible_total_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_shifts', function (Blueprint $table) {
            //
        });
    }

};
