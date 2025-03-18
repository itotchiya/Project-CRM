<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('deal_notes')) {
            Schema::create('deal_notes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->longText('details')->nullable();
                $table->unsignedBigInteger('deal_id')->nullable();
                $table->unsignedInteger('added_by')->nullable();
                $table->unsignedInteger('last_updated_by')->nullable();
                $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade')->onUpdate('cascade');
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_notes');
    }

};
