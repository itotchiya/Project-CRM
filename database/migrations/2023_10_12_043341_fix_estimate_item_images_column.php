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

        Schema::table('task_comments', function (Blueprint $table) {
            $table->longText('comment')->change();
        });

        Schema::table('task_notes', function (Blueprint $table) {
            $table->longText('note')->nullable()->change();
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->longText('department')->nullable()->change();
            $table->longText('designation')->nullable()->change();
            $table->longText('role')->nullable()->change();
        });


        Schema::table('estimate_item_images', function (Blueprint $table) {
            $table->string('filename')->nullable()->change();
        });

        Schema::table('invoice_item_images', function (Blueprint $table) {
            $table->string('filename')->nullable()->change();
        });
    }

};
