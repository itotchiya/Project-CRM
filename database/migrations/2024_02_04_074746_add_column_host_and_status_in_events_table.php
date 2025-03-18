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
        Schema::table('events', function (Blueprint $table) {
            $table->integer('host')->unsigned()->nullable()->after('end_date_time');
            $table->foreign(['host'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->after('host');
            $table->string('note')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('host', 'status', 'note');
        });
    }

};
