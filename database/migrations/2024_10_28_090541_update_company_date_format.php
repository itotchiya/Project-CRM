<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\GlobalSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

            $companies = Company::whereIn('date_format', ['D/M/Y', 'D.M.Y', 'D-M-Y', 'D M Y'])->get();
            $globalSetting = GlobalSetting::whereIn('date_format', ['D/M/Y', 'D.M.Y', 'D-M-Y', 'D M Y'])->get();

            if($companies){
                foreach ($companies as $company) {
                    $company->date_format = 'D d M Y';
                    $company->date_picker_format = 'D dd M yyyy';
                    $company->moment_format = 'ddd DD MMMM YYYY';
                    $company->save();
                    $company->refresh();

                }
            }

            if($globalSetting){
                foreach ($globalSetting as $setting) {
                    $setting->date_format = 'D d M Y';
                    $setting->moment_format = 'ddd DD MMMM YYYY';
                    $setting->save();
                    $setting->refresh();

                }
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
