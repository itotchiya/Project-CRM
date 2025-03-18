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
        Schema::table('push_notification_settings', function (Blueprint $table) {
            $table->enum('beams_push_status', ['active', 'inactive'])->default('inactive');
            $table->string('instance_id')->nullable();
            $table->string('beam_secret')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_notification_settings', function (Blueprint $table) {
            $table->dropColumn('beams_push_status');
            $table->dropColumn('instance_id');
            $table->dropColumn('beam_secret');
        });
    }
};
