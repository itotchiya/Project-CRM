<?php

namespace App\Observers;

use App\Models\TicketFile;
use App\Helper\Files;

class TicketFileObserver
{


    public function deleting(TicketFile $file)
    {

        Files::deleteFile($file->hashname, 'ticket-files/' . $file->ticket_reply_id);

        $files = TicketFile::where('ticket_reply_id', $file->ticket_reply_id)->count();

        if($files == 0){
            Files::deleteDirectory(TicketFile::FILE_PATH . '/' . $file->task_id);
        }

    }

}
