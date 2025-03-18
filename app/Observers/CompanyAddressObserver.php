<?php

namespace App\Observers;

use App\Models\CompanyAddress;
use App\Models\EmployeeDetails;

class CompanyAddressObserver
{

    public function creating(CompanyAddress $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    public function deleting(CompanyAddress $model){

        $companyAddress = CompanyAddress::where('is_default', 1)->where('company_id', company()->id)->first();
        EmployeeDetails::where('company_address_id', $model->id)->update(['company_address_id' => $companyAddress->id]);
    }
}
