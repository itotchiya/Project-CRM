<?php

use App\Models\Company;
use App\Models\GlobalSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {

        $this->faviconToCompany();

        if (!Schema::hasColumn('log_time_for', 'tracker_reminder')) {
            Schema::table('log_time_for', function (Blueprint $table) {
                $table->boolean('tracker_reminder')->after('approval_required');
                $table->time('time')->nullable();
            });

        }

        if (!Schema::hasColumn('lead_notes', 'details')) {

            Schema::table('lead_notes', function (Blueprint $table) {
                $table->longText('details')->change();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_time_for', function (Blueprint $table) {
            $table->dropColumn('tracker_reminder');
            $table->dropColumn('time');
        });
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

};
