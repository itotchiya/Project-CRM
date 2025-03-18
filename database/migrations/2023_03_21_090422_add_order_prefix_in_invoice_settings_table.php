<?php

use App\Models\Order;
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
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->string('order_prefix')->default('ODR')->after('contract_digit');
            $table->string('order_number_separator')->default('#')->after('order_prefix');
            $table->unsignedInteger('order_digit')->default(3)->after('order_number_separator');
        });


        if (!Schema::hasColumn('orders', 'custom_order_number')) {

            Schema::table('orders', function (Blueprint $table) {
                $table->string('custom_order_number')->nullable()->after('company_address_id');
            });


            Order::query()
                ->update([
                    'custom_order_number' => DB::raw("CONCAT('ODR#00', order_number)")
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->dropColumn(['order_prefix', 'order_number_separator', 'order_digit']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('custom_order_number');
        });
    }

};
