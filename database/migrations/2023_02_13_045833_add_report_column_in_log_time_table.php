<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('log_time_for', function (Blueprint $table) {
            $table->boolean('timelog_report')->after('tracker_reminder');
            $table->string('daily_report_roles')->nullable()->after('timelog_report');
        });

        Schema::table('users_chat', function (Blueprint $table) {
            $table->boolean('notification_sent')->default(1);
        });

        Schema::table('message_settings', function (Blueprint $table) {
            $table->boolean('send_sound_notification')->default(0);
        });

        DB::statement("ALTER TABLE smtp_settings CHANGE COLUMN mail_encryption mail_encryption ENUM('ssl', 'tls','starttls') NULL DEFAULT 'tls'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_time_for', function (Blueprint $table) {
            $table->dropColumn('timelog_report');
            $table->dropColumn('daily_report_roles');
        });

        Schema::table('users_chat', function (Blueprint $table) {
            $table->dropColumn('notification_sent');
        });

        Schema::table('message_settings', function (Blueprint $table) {
            $table->dropColumn('send_sound_notification');
        });

    }

};
