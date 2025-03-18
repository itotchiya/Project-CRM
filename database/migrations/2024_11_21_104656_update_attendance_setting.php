<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\AttendanceSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = Company::all();

        foreach($companies as $company){

            AttendanceSetting::where('company_id', $company->id)
            ->where('qr_enable', 1)
            ->where('auto_clock_in', 'yes')
            ->update([
                'auto_clock_in' => 'no',
            ]);

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
