<?php

namespace App\Events;

use App\Models\Company;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewCompanyCreatedEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

}
