<?php

use App\Models\GlobalSetting;
use App\Models\Company;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $defaultDriver = config('session.driver');

        if ($defaultDriver != 'database') {
            $defaultDriver = 'file';
        }

        if (!Schema::hasTable('global_settings')) {
            Schema::create('global_settings', function (Blueprint $table) use ($defaultDriver) {
                $table->id();
                $table->string('global_app_name')->nullable();
                $table->string('logo')->nullable();
                $table->string('light_logo')->nullable();
                $table->string('login_background')->nullable();
                $table->string('logo_background_color')->nullable('#ffffff');
                $table->string('sidebar_logo_style')->nullable()->default('square');
                $table->string('locale')->default('en');
                $table->string('purchase_code', 100)->nullable();
                $table->timestamp('supported_until')->nullable();
                $table->enum('google_recaptcha_status', ['active', 'deactive'])->default('deactive');
                $table->enum('google_recaptcha_v2_status', ['active', 'deactive'])->default('deactive');
                $table->string('google_recaptcha_v2_site_key')->nullable();
                $table->string('google_recaptcha_v2_secret_key')->nullable();
                $table->enum('google_recaptcha_v3_status', ['active', 'deactive'])->default('deactive');
                $table->string('google_recaptcha_v3_site_key')->nullable();
                $table->string('google_recaptcha_v3_secret_key')->nullable();
                $table->boolean('app_debug')->default(false);
                $table->string('currency_converter_key');
                $table->string('currency_key_version')->default('free');
                $table->string('moment_format')->default('DD-MM-YYYY');
                $table->string('timezone')->default('Asia/Kolkata');
                $table->boolean('rtl')->default(false);
                $table->string('license_type', 20)->nullable();
                $table->boolean('hide_cron_message')->default(0);
                $table->boolean('system_update')->default(1);
                $table->boolean('show_review_modal')->default(1);
                $table->timestamp('last_cron_run')->nullable()->default(null);
                $table->string('favicon')->nullable();
                $table->enum('auth_theme', ['dark', 'light'])->default('light');
                $table->enum('session_driver', ['file', 'database'])->default($defaultDriver);
                $table->text('allowed_file_types')->nullable();
                $table->integer('allowed_file_size')->default(10);
                $table->boolean('show_update_popup')->default(1);
                $table->timestamps();
            });
        }

        $company = Company::first();


        if ($company) {
            $globalSetting = GlobalSetting::first();

            if (!$globalSetting) {
                $globalSetting = new GlobalSetting();
            }

            $globalSetting->global_app_name = $company->company_name;
            $globalSetting->logo = $company->logo;
            $globalSetting->login_background = $company->login_background;
            $globalSetting->logo_background_color = $company->logo_background_color;
            $globalSetting->sidebar_logo_style = $company->sidebar_logo_style;
            $globalSetting->locale = $company->locale;

            $globalSetting->purchase_code = $company->purchase_code;
            $globalSetting->supported_until = $company->supported_until;
            /** @phpstan-ignore-next-line */
            $globalSetting->license_type = $company->license_type;

            $globalSetting->google_recaptcha_status = $company->google_recaptcha_status ?? 'deactive';
            $globalSetting->google_recaptcha_v2_status = $company->google_recaptcha_v2_status ?? 'deactive';
            $globalSetting->google_recaptcha_v2_site_key = $company->google_recaptcha_v2_site_key;
            $globalSetting->google_recaptcha_v2_secret_key = $company->google_recaptcha_v2_secret_key;
            $globalSetting->google_recaptcha_v3_status = $company->google_recaptcha_v3_status ?? 'deactive';
            $globalSetting->google_recaptcha_v3_site_key = $company->google_recaptcha_v3_site_key;
            $globalSetting->google_recaptcha_v3_secret_key = $company->google_recaptcha_v3_secret_key;
            $globalSetting->app_debug = $company->app_debug ?? false;
            $globalSetting->currency_converter_key = $company->currency_converter_key ?? '';
            /** @phpstan-ignore-next-line */
            $globalSetting->currency_key_version = $company->currency_key_version ?? 'free';
            $globalSetting->light_logo = $company->light_logo;
            $globalSetting->rtl = $company->rtl ?? false;

            /** @phpstan-ignore-next-line */
            $globalSetting->hide_cron_message = $company->hide_cron_message ?? 0;
            $globalSetting->system_update = $company->system_update ?? 1;
            $globalSetting->show_review_modal = $company->show_review_modal ?? 1;
            $globalSetting->last_cron_run = $company->last_cron_run;
            $globalSetting->favicon = $company->favicon;
            $globalSetting->moment_format = $company->moment_format;
            $globalSetting->timezone = $company->timezone;
            $globalSetting->auth_theme = $company->auth_theme ?? 'light';
            $globalSetting->session_driver = $company->session_driver ?? $defaultDriver;
            /** @phpstan-ignore-next-line */
            $globalSetting->allowed_file_types = $company->allowed_file_types ?: 'image/*,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/docx,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-zip-compressed, application/x-compressed, multipart/x-zip,.xlsx,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,application/sla,.stl';
            /** @phpstan-ignore-next-line */
            $globalSetting->allowed_file_size = $company->allowed_file_size ?? 10;
            /** @phpstan-ignore-next-line */
            $globalSetting->show_update_popup = $company->show_update_popup ?? 1;
            $globalSetting->save();
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_code',
                'supported_until',
                'google_recaptcha_status',
                'google_recaptcha_v2_status',
                'google_recaptcha_v2_site_key',
                'google_recaptcha_v2_secret_key',
                'google_recaptcha_v3_status',
                'google_recaptcha_v3_site_key',
                'google_recaptcha_v3_secret_key',
                'app_debug',
                'currency_converter_key',
                'currency_key_version',
                'license_type',
                'hide_cron_message',
                'system_update',
                'show_review_modal',
                'last_cron_run',
                'session_driver',
                'allowed_file_size',
                'allowed_file_types']);

            if (Schema::hasColumn('companies', 'show_update_popup')) {
                $table->dropColumn('show_update_popup');
            }

            if (Schema::hasColumn('companies', 'weather_key')) {
                $table->dropColumn('weather_key');
            }
        });

        Schema::table('global_settings', function (Blueprint $table) {
            $table->timestamp('last_license_verified_at')->nullable()->default(null)->after('supported_until');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('global_settings');
    }

};
