<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('companies')->whereNotNull('headers')->update(['headers' => null]);
        DB::table('users')->whereNotNull('headers')->update(['headers' => null]);
    }

};
