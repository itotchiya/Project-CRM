<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Seeder;
use App\Models\Company;
use Illuminate\Support\Facades\App;

class OrganisationSettingsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $defaultDriver = config('session.driver') == 'database' ? 'database' : 'file';

        $appName = 'Worksuite';

        $globalSetting = new GlobalSetting();
        $globalSetting->global_app_name = $appName;
        $globalSetting->locale = 'en';
        $globalSetting->google_recaptcha_status = 'deactive';
        $globalSetting->google_recaptcha_v2_status = 'deactive';
        $globalSetting->google_recaptcha_v3_status = 'deactive';
        $globalSetting->app_debug = false;
        $globalSetting->rtl = false;
        $globalSetting->hide_cron_message = 0;
        $globalSetting->system_update = 1;
        $globalSetting->show_review_modal = 1;
        $globalSetting->auth_theme = 'light';
        $globalSetting->session_driver = $defaultDriver;
        $globalSetting->allowed_file_size = 10;
        $globalSetting->moment_format = 'DD-MM-YYYY';
        $globalSetting->allowed_file_types = 'image/*,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/docx,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-zip-compressed, application/x-compressed, multipart/x-zip,.xlsx,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,application/sla,.stl';
        $globalSetting->show_update_popup = 1;
        $globalSetting->hash = md5(microtime());
        $globalSetting->save();

        $setting = new Company();
        $setting->company_name = $appName;
        $setting->app_name = $appName;
        $setting->company_email = 'company@email.com';
        $setting->company_phone = '1234567891';
        $setting->address = 'Your Company address here';
        $setting->website = 'https://worksuite.biz';
        $setting->date_format = 'd-m-Y';

        $setting->save();


        if (!App::environment('codecanyon')) {
            $seedCount = config('app.extra_company_seed_count');

            for ($i = 0; $i < $seedCount; $i++) {
                $this->command->info('Seeding company: ' . ($i + 1) . ' Remaining:' . ($seedCount - $i));

                $companyName = fake()->company();

                Company::create([
                    'company_name' => $companyName,
                    'app_name' => $companyName,
                    'company_email' => fake()->unique()->safeEmail(),
                    'company_phone' => fake()->phoneNumber(),
                    'address' => fake()->address(),
                    'created_at' => fake()->dateTimeBetween(now()->subMonths(5), now()),
                    'website' => 'https://worksuite.biz',
                ]);
            }
        }
    }

}
