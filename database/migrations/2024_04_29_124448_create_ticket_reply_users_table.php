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
        Schema::create('ticket_reply_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('ticket_reply_id')->index('ticket_reply_users_ticket_reply_id_foreign');
            $table->unsignedInteger('user_id')->index('ticket_reply_users_user_id_foreign');
            $table->foreign(['ticket_reply_id'])->references(['id'])->on('ticket_replies')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_reply_users');
    }

};
