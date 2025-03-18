<?php

namespace App\Traits;

use App\Models\DealHistory;

trait DealHistoryTrait
{

    static public function createDealHistory($dealId, string $eventType, $fileId = null, $stageFromId = null, $stageToId = null, $taskId = null, $followUpId = null, $noteId = null, $agentId = null, $proposalId = null): void
    {
        DealHistory::create([
            'deal_id' => $dealId,
            'event_type' => $eventType,
            'created_by' => user()->id,
            'deal_stage_from_id' => $stageFromId,
            'deal_stage_to_id' => $stageToId,
            'note_id' => $noteId,
            'file_id' => $fileId,

            'task_id' => $taskId,
            'follow_up_id' => $followUpId,
            'agent_id' => $agentId,
            'proposal_id' => $proposalId,
        ]);

    }

}
