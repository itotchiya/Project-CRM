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
        \App\Models\User::withoutGlobalScopes()->where('customised_permissions', 0)->update(['permission_sync' => 0]);

        Artisan::call('sync-user-permissions', ['all' => true]);

        Schema::table('expenses', function (Blueprint $table) {
            $table->longtext('description')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            //
        });
    }

};
