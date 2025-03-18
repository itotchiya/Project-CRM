<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->enum('payment_status', [1, 0])->default(0)->after('custom_invoice_number');
            $table->unsignedInteger('offline_method_id')->nullable()->index('payments_offline_method_id_foreign')->after('payment_status');
            $table->foreign(['offline_method_id'])->references(['id'])->on('offline_payment_methods')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->string('transaction_id')->nullable()->unique()->after('offline_method_id');
            $table->string('gateway')->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['offline_method_id']);
            $table->dropColumn(['payment_status', 'offline_method_id', 'transaction_id', 'gateway']);
        });
    }

};
