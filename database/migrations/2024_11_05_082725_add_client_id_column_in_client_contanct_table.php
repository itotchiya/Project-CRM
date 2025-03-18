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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('is_client_contact')->nullable()->index('users_is_client_contact_index');
            $table->foreign('is_client_contact')->references('id')->on('client_contacts')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('client_contacts', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable()->index('client_contacts_client_id_index');
            $table->foreign('client_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
