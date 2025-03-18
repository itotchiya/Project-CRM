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
        Schema::table('invoice_recurring', function (Blueprint $table) {
            $table->integer('bank_account_id')->unsigned()->nullable();
            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        Schema::table('expenses_recurring', function (Blueprint $table) {
            $table->integer('bank_account_id')->unsigned()->nullable();
            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_recurring', function (Blueprint $table) {
            $table->dropForeign('invoice_recurring_bank_account_id_foreign');
            $table->dropColumn('bank_account_id');
        });

        Schema::table('expenses_recurring', function (Blueprint $table) {
            $table->dropForeign('expenses_recurring_bank_account_id_foreign');
            $table->dropColumn('bank_account_id');
        });
    }

};
