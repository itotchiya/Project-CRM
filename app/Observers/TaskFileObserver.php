<?php

namespace App\Observers;

use App\Models\TaskFile;
use App\Helper\Files;

class TaskFileObserver
{

    public function saving(TaskFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $file->last_updated_by = user()->id;
        }
    }

    public function creating(TaskFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $file->added_by = $file->user_id;
        }
    }

    public function deleting(TaskFile $file)
    {

        Files::deleteFile($file->hashname, 'task-files/' . $file->task_id);

        if(TaskFile::where('task_id', $file->task_id)->count() == 0){
            Files::deleteDirectory(TaskFile::FILE_PATH . '/' . $file->task_id);
        }

    }

}
