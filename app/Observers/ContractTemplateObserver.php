<?php

namespace App\Observers;

use App\Models\ContractTemplate;

class ContractTemplateObserver
{

    public function creating(ContractTemplate $contract)
    {

        if (user()) {
            $contract->added_by = user()->id;
        }

        if (company()) {
            $contract->company_id = company()->id;
        }

        $contract->contract_template_number = (int)ContractTemplate::max('contract_template_number') + 1;
    }

}
