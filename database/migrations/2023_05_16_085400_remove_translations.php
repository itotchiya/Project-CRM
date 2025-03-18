<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->integer('allow_max_no_of_files')->after('allowed_file_size')->default(10);
        });

        cache()->forget('global_settings');

        Artisan::call('translations:reset');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
