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
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedInteger('project_id')->nullable()->after('group_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });

        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ticket_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('assigned_to')->nullable();
            $table->unsignedInteger('channel_id')->nullable();
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('type_id')->nullable();
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
            $table->string('type')->default('create');
            $table->longText('content')->nullable();
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('channel_id')->references('id')->on('ticket_channels')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('ticket_groups')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('ticket_types')->onDelete('cascade');
            $table->timestamps();
        });

        $tickets = \App\Models\Ticket::get();

        foreach ($tickets as $ticket) {
            $ticketActivity = new \App\Models\TicketActivity();
            $ticketActivity->ticket_id = $ticket->id;
            $ticketActivity->user_id = $ticket->user_id;
            $ticketActivity->assigned_to = $ticket->agent_id;
            $ticketActivity->channel_id = $ticket->channel_id;
            $ticketActivity->group_id = $ticket->group_id;
            $ticketActivity->type_id = $ticket->type_id;
            $ticketActivity->status = $ticket->status;
            $ticketActivity->priority = $ticket->priority;
            $ticketActivity->type = 'create';
            $ticketActivity->save();
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }

};
