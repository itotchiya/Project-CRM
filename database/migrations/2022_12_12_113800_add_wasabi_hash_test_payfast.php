<?php

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
        if (!Schema::hasColumn('global_settings', 'header_color')) {

            DB::statement("ALTER TABLE file_storage CHANGE COLUMN storage_location storage_location ENUM('local', 'aws_s3', 'digitalocean','wasabi') NOT NULL DEFAULT 'local'");

            Schema::table('global_settings', function (Blueprint $table) {
                $table->string('header_color')->after('logo_background_color')->default('#1D82F5');
                $table->string('hash')->after('locale')->nullable();
            });

            $globalSetting = GlobalSetting::first();

            if ($globalSetting) {
                $globalSetting->hash = md5(microtime());
                $globalSetting->saveQuietly();
            }


            Schema::table('companies', function (Blueprint $table) {
                $table->string('header_color')->after('logo_background_color')->default('#1D82F5');
            });

            Schema::table('payment_gateway_credentials', function (Blueprint $table) {
                $table->string('test_payfast_merchant_id')->nullable();
                $table->string('test_payfast_merchant_key')->nullable();
                $table->string('test_payfast_passphrase')->nullable();
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

    }

};
