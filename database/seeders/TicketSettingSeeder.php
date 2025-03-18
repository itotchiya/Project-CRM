<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use App\Models\TicketSettingForAgents;

class TicketSettingSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $companyId = $company->id;

            TicketSettingForAgents::create([
                'ticket_scope' => 'assigned_tickets',
                'company_id' => $companyId,
            ]);
        }
    }

}
