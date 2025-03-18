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
        Schema::create('gantt_links', function (Blueprint $table) {
            $table->id();
            $table->string('type');

            $table->integer('project_id')->unsigned()->nullable();
            $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->integer('source')->unsigned();
            $table->foreign('source')->references('id')->on('tasks')->onDelete('cascade')->onUpdate('cascade');

            $table->integer('target')->unsigned();
            $table->foreign('target')->references('id')->on('tasks')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gantt_links');
    }

};
