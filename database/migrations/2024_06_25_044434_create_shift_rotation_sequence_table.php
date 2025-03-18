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
        Schema::create('shift_rotation_sequences', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_shift_rotation_id')->nullable();
            $table->foreign('employee_shift_rotation_id')->references('id')->on('employee_shift_rotations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('employee_shift_id')->nullable();
            $table->foreign('employee_shift_id')->references('id')->on('employee_shifts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('sequence')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_rotation_sequences');
    }

};
