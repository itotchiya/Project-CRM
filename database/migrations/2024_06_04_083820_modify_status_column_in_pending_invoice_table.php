<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices CHANGE COLUMN status status ENUM('paid', 'unpaid', 'partial', 'canceled', 'draft', 'pending-confirmation') NOT NULL DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
