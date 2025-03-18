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
        DB::table('bank_accounts')
            ->where('account_type', 'credit Card')
            ->update(['account_type' => 'credit-card']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
