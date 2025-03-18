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
        Schema::table('deal_histories', function (Blueprint $table) {
            $table->renameColumn('deal_stage_id', 'deal_stage_from_id');
            $table->unsignedInteger('deal_stage_to_id')->nullable();
            $table->foreign('deal_stage_to_id')->references('id')->on('pipeline_stages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deal_histories', function (Blueprint $table) {
            $table->renameColumn('deal_stage_from_id', 'deal_stage_id');
            $table->dropForeign(['deal_stage_to_id']);
            $table->dropColumn('deal_stage_to_id');
        });
    }

};
