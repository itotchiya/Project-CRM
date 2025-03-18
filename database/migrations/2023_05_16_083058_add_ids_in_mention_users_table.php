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
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn('early_clock_in');
        });

        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->string('early_clock_in')->nullable();
        });

        Schema::table('mention_users', function (Blueprint $table) {
            $table->integer('ticket_id')->unsigned()->nullable()->after('discussion_id');
            $table->foreign('ticket_id')->references('id')->on('tickets')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->integer('event_id')->unsigned()->nullable()->after('ticket_id');
            $table->foreign('event_id')->references('id')->on('events')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mention_users', function (Blueprint $table) {
            //
        });
    }

};
