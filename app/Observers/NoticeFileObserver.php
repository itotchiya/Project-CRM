<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\NoticeFile;

class NoticeFileObserver
{

    public function saving(NoticeFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $file->last_updated_by = user()->id;
        }
    }

    public function creating(NoticeFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $file->added_by = $file->user_id;
        }
    }

    public function deleting(NoticeFile $file)
    {
        Files::deleteFile($file->hashname, 'notice-files/' . $file->notice_id);

        if(NoticeFile::where('notice_id', $file->notice_id)->count() == 0){
            Files::deleteDirectory(NoticeFile::FILE_PATH . '/' . $file->notice_id);
        }

    }

}
