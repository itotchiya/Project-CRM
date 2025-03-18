<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $taxes = [
            ['tax_name' => 'GST', 'rate_percent' => '10', 'company_id' => $companyId],
            ['tax_name' => 'CGST', 'rate_percent' => '18', 'company_id' => $companyId],
            ['tax_name' => 'VAT', 'rate_percent' => '10', 'company_id' => $companyId],
            ['tax_name' => 'IGST', 'rate_percent' => '10', 'company_id' => $companyId],
            ['tax_name' => 'UTGST', 'rate_percent' => '10', 'company_id' => $companyId],
        ];

        Tax::insert($taxes);

    }

}
