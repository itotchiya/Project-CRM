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

        if (!Schema::hasColumn('deals', 'deal_watcher')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->integer('deal_watcher')->nullable();
            });
        }

        if (!Schema::hasColumn('project_milestones', 'add_to_budget')) {
            Schema::table('project_milestones', function (Blueprint $table) {
                $table->enum('add_to_budget', ['yes', 'no'])->default('no')->after('status');
            });
        }

        Schema::table('leave_types', function (Blueprint $table) {
            $table->decimal('monthly_limit', 10, 2)->change();
        });

        Schema::table('project_time_logs', function (Blueprint $table) {
            $table->double('earnings', 16, 2)->change();
        });

        if (!Schema::hasColumn('attendance_settings', 'qr_enable')) {
            Schema::table('attendance_settings', function (Blueprint $table) {
                $table->enum('qr_enable', ['1', '0'])->default('1');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }

};
