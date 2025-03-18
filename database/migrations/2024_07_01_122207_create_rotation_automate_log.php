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
        Schema::create('rotation_automate_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('user_id')->index('employee_shift_schedules_user_id_foreign');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedInteger('employee_shift_rotation_id')->nullable();
            $table->foreign('employee_shift_rotation_id')->references('id')->on('employee_shift_rotations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('employee_shift_id')->nullable();
            $table->foreign('employee_shift_id')->references('id')->on('employee_shifts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('sequence')->nullable();
            $table->date('cron_run_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rotation_automate_log');
    }

};
