<?php

use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\Team;
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

        $this->googleMapKey();
        $this->subTotalTables();
        $this->lastViewed();
        $this->faviconToCompany();
        $this->noteDetailsToText();
        $this->foreignKeyFixCompaniesTable();

        Team::where('parent_id', 0)->update(['parent_id' => null]);

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

    private function subTotalTables()
    {
        $subTotalTables = ['proposal_templates', 'orders', 'quotations'];

        foreach ($subTotalTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->double('sub_total', 16, 2)->change();
            });
        }
    }

    private function googleMapKey()
    {
        if (!Schema::hasColumn('global_settings', 'google_map_key')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->string('google_map_key')->nullable();
            });
        }

        $company = Company::first();

        if ($company) {
            $globalSetting = GlobalSetting::first();
            $globalSetting->google_map_key = $company->google_map_key;
            $globalSetting->saveQuietly();
        }
    }

    private function faviconToCompany()
    {
        if (!Schema::hasColumn('companies', 'favicon')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('favicon')->nullable()->after('logo');
            });

            $globalSetting = GlobalSetting::select('id', 'favicon')->first();

            if ($globalSetting) {
                $company = Company::first();
                $company->favicon = $globalSetting->favicon;
                $company->saveQuietly();
            }
        }
    }

    private function lastViewed()
    {
        // Add last viewed and other info
        $lastViewedTables = ['proposals', 'invoices', 'estimates'];

        foreach ($lastViewedTables as $tableName) {
            if (!Schema::hasColumn($tableName, 'last_viewed')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->timestamp('last_viewed')->nullable();
                    $table->string('ip_address')->nullable();
                });
            }
        }
    }

    private function noteDetailsToText()
    {
        Schema::table('lead_notes', function (Blueprint $table) {
            $table->text('details')->change();
        });
    }

    private function foreignKeyFixCompaniesTable()
    {

        $currencyTables = GlobalSetting::CURRENCY_TABLES;

        // We are restricting the currency id delete to prevent deleting records
        foreach ($currencyTables as $currencyTable) {
            try {


                Schema::table($currencyTable, function (Blueprint $table) {
                    $table->dropForeign(['currency_id']);
                    $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

};
