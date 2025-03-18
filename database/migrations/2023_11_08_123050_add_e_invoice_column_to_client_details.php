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
        Schema::whenTableDoesntHaveColumn('company_addresses', 'country_id', function (Blueprint $table) {
            $table->unsignedInteger('country_id')->nullable()->after('company_id');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
        });

        Schema::whenTableDoesntHaveColumn('client_details', 'electronic_address', function (Blueprint $table) {
            $table->string('electronic_address')->nullable();
            $table->string('electronic_address_scheme')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn('client_details', 'electronic_address', function (Blueprint $table) {
            $table->dropColumn('electronic_address');
            $table->dropColumn('electronic_address_scheme');
        });
    }

};
