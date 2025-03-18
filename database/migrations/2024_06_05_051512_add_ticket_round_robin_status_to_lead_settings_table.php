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
        Schema::table('lead_setting', function (Blueprint $table) {
            $table->tinyInteger('ticket_round_robin_status')->default(0)->notNull();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_setting', function (Blueprint $table) {
            $table->dropColumn('ticket_round_robin_status');
        });
    }

};
