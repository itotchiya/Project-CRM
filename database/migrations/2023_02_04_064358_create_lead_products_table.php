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
        Schema::create('lead_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('lead_id')->index('lead_products_lead_id_foreign');
            $table->unsignedInteger('product_id')->index('lead_products_product_id_foreign');
            $table->foreign(['lead_id'])->references(['id'])->on('leads')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_products');
    }

};
