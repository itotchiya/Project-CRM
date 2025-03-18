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
        Schema::table('project_time_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('weekly_timesheet_id')->nullable();
            $table->foreign('weekly_timesheet_id')->references('id')->on('weekly_timesheets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_time_logs', function (Blueprint $table) {
            $table->dropForeign(['weekly_timesheet_id']);
            $table->dropColumn('weekly_timesheet_id');
        });
    }
};
