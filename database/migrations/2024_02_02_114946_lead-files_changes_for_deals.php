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
        if (Schema::hasTable('lead_files')) {
            Schema::rename('lead_files', 'deal_files');

            try {
                Schema::table('deal_files', function (Blueprint $table) {
                    $table->dropForeign(['lead_id']);
                });
            } catch (\Exception $e) {
                echo "\nForeign key lead_id does not exist in lead_files\n";
            }

            Schema::table('deal_files', function (Blueprint $table) {
                $table->renameColumn('lead_id', 'deal_id');
                $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
