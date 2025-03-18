<?php

use App\Models\Currency;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        foreach (['invoices', 'payments', 'expenses'] as $tableName) {
            if (!Schema::hasColumn($tableName, 'default_currency_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('default_currency_id')->unsigned()->nullable()->after('currency_id');
                    $table->foreign('default_currency_id')
                        ->references('id')
                        ->on('currencies')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                    $table->double('exchange_rate')->nullable()->after('default_currency_id');
                });
            }
        }

        $exchangeRates = Currency::whereNull('exchange_rate')->get();

        foreach ($exchangeRates as $exchangeRate) {
            $exchangeRate->exchange_rate = 1;
            $exchangeRate->save();
        }

        $currencies = Currency::select('id', 'exchange_rate')->get();

        foreach ($currencies as $currency) {
            Invoice::where('currency_id', $currency->id)->update(['exchange_rate' => $currency->exchange_rate]);
            Payment::where('currency_id', $currency->id)->update(['exchange_rate' => $currency->exchange_rate]);
            Expense::where('currency_id', $currency->id)->update(['exchange_rate' => $currency->exchange_rate]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (['invoices', 'payments', 'expenses'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign('default_currency_id');
                $table->dropColumn('default_currency_id');
                $table->dropColumn('exchange_rate');
            });
        }
    }

};
