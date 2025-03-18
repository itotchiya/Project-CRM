<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ContractTypeTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {

        $contracts = [
            'Employment contract',
            'Service contract',
            'Construction contract',
            'Sales contract',
            'Lease contract',
            'Purchase agreement',
            'Partnership agreement',
            'Non-disclosure agreement',
            'Non-compete agreement',
            'Joint venture agreement',
            'Franchise agreement',
            'Loan agreement',
            'License agreement',
            'Consulting agreement',
            'Distribution agreement',
            'Supply agreement',
            'Indemnification agreement',
            'Guarantee agreement',
            'Insurance contract',
            'Agency agreement',
            'Master service agreement',
            'Subcontractor agreement',
            'Operating agreement',
            'Shareholders agreement',
            'Employee handbook',
            'Independent contractor agreement',
            'Subscription agreement',
            'Software license agreement',
            'Terms of use',
            'Privacy policy',
            'End-user license agreement',
            'Service level agreement',
            'Maintenance agreement',
            'Support agreement',
            'Professional services agreement',
            'Statement of work',
            'Memorandum of understanding',
            'Letter of intent',
            'Memorandum of agreement'
        ];

        \App\Models\ContractType::insert(
            array_map(function ($value) use ($companyId) {
                return [
                    'company_id' => $companyId,
                    'name' => $value
                ];
            }, $contracts)
        );

    }

}
