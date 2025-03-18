<?php

namespace App\Observers;

use App\Models\KnowledgeBase;

class KnowledgeBaseObserver
{

    public function creating(KnowledgeBase $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

}
