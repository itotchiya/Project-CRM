<?php

use App\Models\Company;
use App\Models\GlobalSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('global_settings', 'license_type')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->string('license_type', 20)->after('purchase_code')->nullable();
            });
        }

        $companies = Company::select('id')->get();
        $tables = GlobalSetting::COMPANY_TABLES;


        // This is done for existing customers whose record for now in database is null for company
        // due to some issues in previous version
        if ($companies->isNotEmpty() && isWorksuite()) {
            foreach ($tables as $table) {
                if (Schema::hasColumn($table, 'company_id')) {
                    DB::table($table)->whereNull('company_id')->update(['company_id' => 1]);
                }
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
