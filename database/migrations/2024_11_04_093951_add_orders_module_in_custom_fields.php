<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CustomFieldGroup;
use App\Models\Order;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = Company::select('id')->get();
        $customFieldGroup = [];

        foreach ($companies as $company) {
            $customFieldGroup = [
                [
                    'name' => 'Order',
                    'model' => Order::CUSTOM_FIELD_MODEL,
                    'company_id' => $company->id
                ]
            ];
        }

        CustomFieldGroup::insert($customFieldGroup);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
