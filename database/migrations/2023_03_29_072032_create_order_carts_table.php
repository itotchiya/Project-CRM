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

        Schema::create(
            'order_carts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id')->index('order_carts_product_id_foreign');
                $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->unsignedInteger('client_id')->nullable()->index('order_carts_client_id_foreign');
                $table->foreign(['client_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->string('item_name');
                $table->text('description')->nullable();
                $table->enum('type', ['item', 'discount', 'tax'])->default('item');
                $table->double('quantity', 16, 2);
                $table->double('unit_price', 16, 2);
                $table->double('amount', 16, 2);
                $table->string('taxes')->nullable();
                $table->string('hsn_sac_code')->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {

        Schema::dropIfExists('order_carts');
    }

};
