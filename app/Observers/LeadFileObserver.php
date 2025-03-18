<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\DealFile;
use App\Traits\DealHistoryTrait;

class LeadFileObserver
{

    use DealHistoryTrait;

    public function saving(DealFile $leadFile)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $leadFile->last_updated_by = user()->id;
        }
    }

    public function created(DealFile $leadFile)
    {

        if (!isRunningInConsoleOrSeeding()) {
            self::createDealHistory($leadFile->deal_id, 'file-added', fileId: $leadFile->id);
        }

    }

    public function creating(DealFile $leadFile)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $leadFile->added_by = user()->id;
        }
    }

    public function deleting(DealFile $leadFile)
    {
        Files::deleteFile($leadFile->hashname, DealFile::FILE_PATH . '/' . $leadFile->lead_id);
    }

    public function deleted(DealFile $leadFile)
    {
        if (user()) {
            self::createDealHistory($leadFile->deal_id, 'file-deleted');
        }
    }

}
