<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing records to have the current timestamp
        DB::table('leave_types')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('leave_types')->whereNull('updated_at')->update(['updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }

};
