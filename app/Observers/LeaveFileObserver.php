<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\LeaveFile;

class LeaveFileObserver
{

    public function saving(LeaveFile $leavefile)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $leavefile->last_updated_by = user()->id;
        }

    }

    public function creating(LeaveFile $leavefile)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $leavefile->added_by = user()->id;
        }

        if (company()) {
            $leavefile->company_id = company()->id;
        }
    }

    public function deleting(LeaveFile $leavefile)
    {
        $leavefile->load('leave');

        Files::deleteFile($leavefile->hashname, LeaveFile::FILE_PATH);
        if(LeaveFile::where('leave_id', $leavefile->leave_id)->count() == 0){
            Files::deleteDirectory(LeaveFile::FILE_PATH . '/' . $leavefile->leave_id);
        }

    }

}
