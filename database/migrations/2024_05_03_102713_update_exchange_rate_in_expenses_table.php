<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['expenses', 'payments', 'invoices'];

        foreach ($tables as $table) {
            DB::table($table)->update(['exchange_rate' => DB::raw('ROUND(1 / exchange_rate, 4)')]);
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
