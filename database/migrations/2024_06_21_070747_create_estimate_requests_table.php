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
        if (!Schema::hasTable('estimate_requests')) {
            Schema::create('estimate_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('client_id');
                $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                $table->integer('company_id')->unsigned()->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->integer('estimate_id')->unsigned()->nullable();
                $table->foreign('estimate_id')->references('id')->on('estimates')->onDelete('cascade')->onUpdate('cascade');
                $table->longText('description');
                $table->double('estimated_budget', 16, 2);
                $table->integer('project_id')->unsigned()->nullable();
                $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('cascade')->onDelete('cascade');
                $table->text('early_requirement')->nullable();
                $table->unsignedInteger('currency_id')->nullable();
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null')->onUpdate('cascade');
                $table->enum('status', ['pending', 'rejected', 'accepted', 'in process'])->default('pending');
                $table->text('reason');
                $table->timestamps();
            });
        };
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_requests');
    }

};
