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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('company_sign')->nullable();
            $table->date('sign_date')->nullable();
        });

        Schema::table('contract_signs', function (Blueprint $table) {
            $table->string('place')->nullable();
            $table->string('date')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {

            Schema::dropIfExists('contracts');
            
            Schema::dropIfExists('contract_signs');

    }

};
