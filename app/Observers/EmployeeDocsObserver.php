<?php

namespace App\Observers;

use App\Models\EmployeeDocument;

class EmployeeDocsObserver
{

    public function saving(EmployeeDocument $doc)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $doc->last_updated_by = user()->id;
        }
    }

    public function creating(EmployeeDocument $doc)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $doc->added_by = user()->id;

        }

        if (company()) {
            $doc->company_id = company()->id;
        }
    }

}
