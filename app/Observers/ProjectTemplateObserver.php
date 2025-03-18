<?php

namespace App\Observers;

use App\Models\ProjectTemplate;

class ProjectTemplateObserver
{

    public function creating(ProjectTemplate $projectTemplate)
    {
        if (company()) {
            $projectTemplate->company_id = company()->id;
        }
    }

}
