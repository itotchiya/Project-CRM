<?php

namespace App\Observers;

use App\Models\KnowledgeBaseCategory;

class KnowledgeBaseCategoriesObserver
{

    public function creating(KnowledgeBaseCategory $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

}
