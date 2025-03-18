<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('headers')->nullable();
            $table->string('register_ip')->nullable();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->text('headers')->nullable();
            $table->string('register_ip')->nullable();
        });
    }

};
