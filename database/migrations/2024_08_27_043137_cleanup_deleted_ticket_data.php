<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\TicketFile;
use App\Helper\Files;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all tickets that are deleted
        $deletedTickets = Ticket::onlyTrashed()->get();

        foreach ($deletedTickets as $deletedTicket) {
            // Find all replies related to the deleted ticket
            $ticketReplies = TicketReply::where('ticket_id', $deletedTicket->id)->get();

            foreach ($ticketReplies as $reply) {
                // Delete files associated with the reply
                $this->deleteFilesForReply($reply);

                // Delete the reply itself
                $reply->delete();
            }
        }

        // Get all replies that are deleted
        $deletedReplies = TicketReply::onlyTrashed()->get();

        foreach ($deletedReplies as $deletedReply) {

            // Delete files associated with the reply
            $this->deleteFilesForReply($deletedReply);
        }

    }

    /**
     * Delete files associated with a given reply.
     *
     * @param TicketReply $reply
     */
    private function deleteFilesForReply($reply)
    {
        // Get all files associated with this reply
        $replyFiles = TicketFile::where('ticket_reply_id', $reply->id)->get();

        foreach ($replyFiles as $file) {

            $filePath = TicketFile::FILE_PATH . '/' . $file->ticket_reply_id;

            Files::deleteFile($file->hashname, TicketFile::FILE_PATH);
            Files::deleteDirectory($filePath);
            $file->delete();

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
