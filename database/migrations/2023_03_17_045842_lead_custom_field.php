<?php

use App\Models\Company;
use App\Models\LeadCustomForm;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        $companies = Company::get();

        foreach ($companies as $company) {
            $LeadCustomProductForm = LeadCustomForm::where('company_id', $company->id)->where('field_name', 'product')->first();

            if(is_null($LeadCustomProductForm)){
                LeadCustomForm::create([
                    'field_display_name' => 'Product',
                    'field_name' => 'product',
                    'field_order' => 8,
                    'field_type' => 'select',
                    'company_id' => $company->id,
                ]);
            }

            $LeadCustomSourceForm = LeadCustomForm::where('company_id', $company->id)->where('field_name', 'source')->first();

            if(is_null($LeadCustomSourceForm)){
                LeadCustomForm::create([
                    'field_display_name' => 'Source',
                    'field_name' => 'source',
                    'field_order' => 9,
                    'field_type' => 'select',
                    'company_id' => $company->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        //
    }

};
