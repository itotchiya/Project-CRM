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
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->string('invoice_number_separator')->default('#')->after('invoice_prefix');
            $table->string('estimate_number_separator')->default('#')->after('estimate_prefix');
            $table->string('credit_note_number_separator')->default('#')->after('credit_note_prefix');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->dropColumn('invoice_number_separator');
            $table->dropColumn('estimate_number_separator');
            $table->dropColumn('credit_note_number_separator');
        });
    }

};
