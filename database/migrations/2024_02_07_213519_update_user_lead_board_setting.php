<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('user_leadboard_settings', function (Blueprint $table) {
                if (Schema::hasColumn('user_leadboard_settings', 'board_column_id')) {
                    $table->dropForeign(['board_column_id']);
                    $table->dropColumn('board_column_id');
                }
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

    }

};
