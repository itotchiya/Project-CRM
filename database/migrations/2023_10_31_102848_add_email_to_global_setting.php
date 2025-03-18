<?php

use App\Models\Company;
use App\Models\GlobalSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::whenTableDoesntHaveColumn('global_settings', 'email', function (Blueprint $table) {
            $table->string('email')->nullable();
        });

        Schema::whenTableDoesntHaveColumn('global_settings', 'last_license_verified_at', function (Blueprint $table) {
            $table->datetime('last_license_verified_at')->nullable();
        });

        if (isWorksuite()) {
            $firstCompany = Company::first();

            if ($firstCompany) {
                $globalSetting = GlobalSetting::first();
                $globalSetting->email = $firstCompany->company_email;
                $globalSetting->last_license_verified_at = now()->subDays(2);
                $globalSetting->saveQuietly();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn('global_settings', 'email', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }

};
