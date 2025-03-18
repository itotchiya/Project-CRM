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
            $table->integer('auto_clock_out_time')->default(1)->after('office_end_time');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('auto_clock_out')->default(0)->after('clock_out_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
