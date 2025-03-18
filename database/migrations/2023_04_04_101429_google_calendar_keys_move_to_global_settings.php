<?php

use App\Models\Company;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        if (!Schema::hasColumn('global_settings', 'google_calendar_status')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->enum('google_calendar_status', ['active', 'inactive'])->default('inactive');
                $table->text('google_client_id')->nullable();
                $table->text('google_client_secret')->nullable();
            });

            $company = Company::first();
            $globalSetting = GlobalSetting::first();

            if ($company && $globalSetting) {
                $globalSetting->google_calendar_status = $company->google_calendar_status;
                $globalSetting->google_client_id = $company->google_client_id;
                $globalSetting->google_client_secret = $company->google_client_secret;
                $globalSetting->save();
                cache()->forget('global_setting');
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
