<?php

namespace App\Observers;

use App\Models\InvoiceSetting;
use App\Http\Controllers\AppSettingController;
use App\Models\Role;

class InvoiceSettingObserver
{

    public function creating(InvoiceSetting $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

    public function updated(InvoiceSetting $invoiceSetting)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if ($invoiceSetting->isDirty('template')) {
                $role = Role::with('roleuser')->where('name', 'client')->first();
                $roleUsers = $role->roleuser->pluck('user_id')->toArray();
                $deleteSessions = new AppSettingController();
                $deleteSessions->deleteSessions($roleUsers);
            }
        }
    }

}
