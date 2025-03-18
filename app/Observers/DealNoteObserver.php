<?php

namespace App\Observers;

use App\Models\DealNote;
use App\Traits\DealHistoryTrait;

class DealNoteObserver
{

    use DealHistoryTrait;

    /**
     * @param DealNote $dealNote
     */
    public function saving(DealNote $dealNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $dealNote->last_updated_by = user()->id;
            }
        }
    }

    public function created(DealNote $dealNote)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (user()) {
                self::createDealHistory($dealNote->deal_id, 'note-added', noteId: $dealNote->id);
            }
        }
    }

    public function creating(DealNote $dealNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $dealNote->added_by = user()->id;
            }
        }
    }

    public function deleted(DealNote $dealNote)
    {
        if (user()) {
            self::createDealHistory($dealNote->deal_id, 'note-deleted');
        }
    }

}
