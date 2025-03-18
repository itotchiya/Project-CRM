<?php

/*
|--------------------------------------------------------------------------
| Register Namespaces And Routes
|--------------------------------------------------------------------------
|
| When a module starting, this file will executed automatically. This helps
| to register some namespaces like translator or view. Also this file
| will load the routes file for each module. You may also modify
| this file as you want.
|
*/

use App\Helper\Files;
use App\Models\AttendanceSetting;
use App\Models\Company;
use App\Models\Currency;
use App\Models\CustomLinkSetting;
use App\Models\GdprSetting;
use App\Models\InvoiceSetting;
use App\Models\LanguageSetting;
use App\Models\LogTimeFor;
use App\Models\Permission;
use App\Models\QuickBooksSetting;
use App\Models\SocialAuthSetting;
use App\Models\StorageSetting;
use App\Models\ThemeSetting;
use App\Models\UserPermission;
use App\Scopes\CompanyScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (!function_exists('user')) {

    /**
     * Return current logged-in user
     */
    function user()
    {
        if (session()->has('user')) {
            return session('user');
        }

        $user = auth()->user();

        if ($user) {

            if (session()->has('clientContact')) {
                session()->forget('clientContact');
            }

            if (!is_null($user->is_client_contact)) {
                session(['clientContact' => $user->clientContact]);
                $user = $user->clientContact->client;
            }

            session(['user' => $user]);

            return session('user');
        }

        return null;
    }

}

if (!function_exists('user_roles')) {

    /**
     * Return current logged in user
     */
    // @codingStandardsIgnoreLine
    function user_roles()
    {
        if (session()->has('user_roles')) {
            return session('user_roles');
        }

        $user = user();

        if ($user) {
            $roles = user()->roles;
            session(['user_roles' => $roles->pluck('name')->toArray()]);
            session(['user_role_ids' => $roles->pluck('id')->toArray()]);

            return session('user_roles');
        }

        return null;
    }

}

if (!function_exists('admin_theme')) {

    // @codingStandardsIgnoreLine
    function admin_theme()
    {
        if (!session()->has('admin_theme')) {
            session(['admin_theme' => ThemeSetting::where('panel', 'admin')->first()]);
        }

        return session('admin_theme');
    }

}

if (!function_exists('employee_theme')) {

    // @codingStandardsIgnoreLine
    function employee_theme()
    {
        if (!session()->has('employee_theme')) {
            session(['employee_theme' => ThemeSetting::where('panel', 'employee')->first()]);
        }

        return session('employee_theme');
    }

}

if (!function_exists('client_theme')) {

    // @codingStandardsIgnoreLine
    function client_theme()
    {
        if (!session()->has('client_theme')) {
            session(['client_theme' => ThemeSetting::where('panel', 'client')->first()]);
        }

        return session('client_theme');
    }

}

if (!function_exists('global_setting')) {

    // @codingStandardsIgnoreLine
    function global_setting()
    {

        if (!cache()->has('global_setting')) {
            $setting = \App\Models\GlobalSetting::first();
            cache(['global_setting' => $setting]);

            return $setting;
        }

        return cache('global_setting');
    }

}

if (!function_exists('push_setting')) {

    // @codingStandardsIgnoreLine
    function push_setting()
    {
        if (!cache()->has('push_setting')) {
            cache(['push_setting' => \App\Models\PushNotificationSetting::first()]);
        }

        return cache('push_setting');
    }

}

if (!function_exists('language_setting')) {

    // @codingStandardsIgnoreLine
    function language_setting()
    {
        if (!cache()->has('language_setting')) {
            cache(['language_setting' => \App\Models\LanguageSetting::where('status', 'enabled')->get()]);
        }

        return cache('language_setting');
    }

}

if (!function_exists('language_setting_locale')) {

    // @codingStandardsIgnoreLine
    function language_setting_locale($locale)
    {
        if (!cache()->has('language_setting_' . $locale)) {
            cache(['language_setting_' . $locale => \App\Models\LanguageSetting::where('language_code', $locale)->first()]);
        }

        return cache('language_setting_' . $locale);
    }

}

if (!function_exists('smtp_setting')) {

    // @codingStandardsIgnoreLine
    function smtp_setting()
    {
        if (!session()->has('smtp_setting')) {
            session(['smtp_setting' => \App\Models\SmtpSetting::first()]);
        }

        return session('smtp_setting');
    }

}

if (!function_exists('message_setting')) {

    // @codingStandardsIgnoreLine
    function message_setting()
    {
        if (!session()->has('message_setting')) {
            session(['message_setting' => \App\Models\MessageSetting::first()]);
        }

        return session('message_setting');
    }

}

if (!function_exists('storage_setting')) {

    // @codingStandardsIgnoreLine
    function storage_setting()
    {
        if (!session()->has('storage_setting')) {
            $setting = StorageSetting::where('status', 'enabled')->first();

            session(['storage_setting' => $setting]);
        }

        return session('storage_setting');
    }

}

if (!function_exists('email_notification_setting')) {

    // @codingStandardsIgnoreLine
    function email_notification_setting()
    {

        if (in_array('client', user_roles()) || in_array('employee', user_roles())) {
            if (!session()->has('email_notification_setting')) {
                session(['email_notification_setting' => \App\Models\EmailNotificationSetting::all()]);
            }
        }

        if (!session()->has('email_notification_setting')) {
            session(['email_notification_setting' => \App\Models\EmailNotificationSetting::all()]);
        }

        return session('email_notification_setting');
    }

}


if (!function_exists('asset_url')) {

    // @codingStandardsIgnoreLine
    function asset_url($path)
    {
        $path = \App\Helper\Files::UPLOAD_FOLDER . '/' . $path;
        $storageUrl = $path;

        if (!Str::startsWith($storageUrl, 'http')) {
            return url($storageUrl);
        }

        return $storageUrl;
    }

}

if (!function_exists('user_modules')) {

    // @codingStandardsIgnoreLine
    function user_modules()
    {
        $user = user();

        if (!$user) {
            return [];
        }

        if (cache()->has('user_modules_' . $user->id)) {
            return cache('user_modules_' . $user->id);
        }

        $module = new \App\Models\ModuleSetting();

        if (in_array('admin', user_roles())) {
            $module = $module->where('type', 'admin');

        }
        elseif (in_array('client', user_roles())) {
            $module = $module->where('type', 'client');

        }
        elseif (in_array('employee', user_roles())) {
            $module = $module->where('type', 'employee');
        }

        $module = $module->where('status', 'active');
        $module->select('module_name');

        $module = $module->get();
        $moduleArray = [];

        foreach ($module->toArray() as $item) {
            $moduleArray[] = array_values($item)[0];
        }

        cache()->put('user_modules_' . $user->id, $moduleArray);

        return $moduleArray;
    }

}

if (!function_exists('worksuite_plugins')) {

    // @codingStandardsIgnoreLine
    function worksuite_plugins()
    {

        if (!cache()->has('worksuite_plugins')) {
            $plugins = \Nwidart\Modules\Facades\Module::allEnabled();
            cache(['worksuite_plugins' => array_keys($plugins)]);
        }

        return cache('worksuite_plugins');
    }

}

if (!function_exists('pusher_settings')) {

    // @codingStandardsIgnoreLine
    function pusher_settings()
    {
        if (!session()->has('pusher_settings')) {
            session(['pusher_settings' => \App\Models\PusherSetting::first()]);
        }

        return session('pusher_settings');
    }

}


if (!function_exists('isSeedingData')) {

    /**
     * Check if app is seeding data
     * @return boolean
     */
    function isSeedingData()
    {
        // We set config(['app.seeding' => true]) at the beginning of each seeder. And check here
        return config('app.seeding');
    }

}

if (!function_exists('isRunningInConsoleOrSeeding')) {

    /**
     * Check if app is seeding data
     * @return boolean
     */
    function isRunningInConsoleOrSeeding()
    {
        // We set config(['app.seeding' => true]) at the beginning of each seeder. And check here
        return app()->runningInConsole() || isSeedingData();
    }

}

if (!function_exists('asset_url_local_s3')) {

    // @codingStandardsIgnoreLine
    function asset_url_local_s3($path)
    {
        if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {
            // Check if the URL is already cached
            if (\Illuminate\Support\Facades\Cache::has(config('filesystems.default') . '-' . $path)) {
                $temporaryUrl = \Illuminate\Support\Facades\Cache::get(config('filesystems.default') . '-' . $path);
            }
            else {
                // Generate a new temporary URL and cache it
                $temporaryUrl = Storage::disk(config('filesystems.default'))->temporaryUrl($path, now()->addMinutes(StorageSetting::HASH_TEMP_FILE_TIME));
                \Illuminate\Support\Facades\Cache::put(config('filesystems.default') . '-' . $path, $temporaryUrl, StorageSetting::HASH_TEMP_FILE_TIME * 60);
            }

            return $temporaryUrl;
        }

        $path = Files::UPLOAD_FOLDER . '/' . $path;
        $storageUrl = $path;

        if (!Str::startsWith($storageUrl, 'http')) {
            return url($storageUrl);
        }

        return $storageUrl;
    }

}

if (!function_exists('download_local_s3')) {

    // @codingStandardsIgnoreLine
    function download_local_s3($file, $path)
    {

        if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {
            return Storage::disk(config('filesystems.default'))->download($path, basename($file->filename));
        }

        $path = Files::UPLOAD_FOLDER . '/' . $path;
        $ext = pathinfo($file->filename, PATHINFO_EXTENSION);

        $filename = $file->name ? $file->name . '.' . $ext : $file->filename;
        try {
            return response()->download($path, $filename);
        } catch (\Exception $e) {
            return response()->view('errors.file_not_found', ['message' => $e->getMessage()], 404);
        }

    }

}


if (!function_exists('gdpr_setting')) {

    // @codingStandardsIgnoreLine
    function gdpr_setting()
    {
        if (!session()->has('gdpr_setting')) {
            session(['gdpr_setting' => GdprSetting::first()]);
        }

        return session('gdpr_setting');
    }

}

if (!function_exists('social_auth_setting')) {

    // @codingStandardsIgnoreLine
    function social_auth_setting()
    {
        if (!cache()->has('social_auth_setting')) {
            cache(['social_auth_setting' => SocialAuthSetting::first()]);
        }

        return cache('social_auth_setting');
    }

}

if (!function_exists('invoice_setting')) {

    // @codingStandardsIgnoreLine
    function invoice_setting()
    {
        if (!session()->has('invoice_setting')) {
            $setting = InvoiceSetting::first();
            session(['invoice_setting' => $setting]);

            return $setting;
        }

        return session('invoice_setting');
    }

    // @codingStandardsIgnoreLine

}

if (!function_exists('time_log_setting')) {

    // @codingStandardsIgnoreLine
    function time_log_setting()
    {
        if (!session()->has('time_log_setting')) {
            session(['time_log_setting' => LogTimeFor::first()]);
        }

        return session('time_log_setting');
    }

}

if (!function_exists('check_migrate_status')) {

    // @codingStandardsIgnoreLine
    function check_migrate_status()
    {

        if (!session()->has('check_migrate_status')) {

            $status = Artisan::call('migrate:check');

            if ($status && !request()->ajax()) {
                Artisan::call('migrate', ['--force' => true, '--schema-path' => 'do not run schema path']); // Migrate database
                Artisan::call('optimize:clear');
            }

            session(['check_migrate_status' => 'Good']);
        }

        return session('check_migrate_status');
    }

}

if (!function_exists('countries')) {

    // @codingStandardsIgnoreLine
    function countries()
    {
        if (!cache()->has('countries')) {
            cache(['countries' => \App\Models\Country::all()]);
        }

        return cache('countries');
    }

}

if (!function_exists('module_enabled')) {

    // @codingStandardsIgnoreLine
    function module_enabled($moduleName)
    {
        return \Nwidart\Modules\Facades\Module::collections()->has($moduleName);
    }

}

if (!function_exists('currency_format_setting')) {

    // @codingStandardsIgnoreLine
    function currency_format_setting($currencyId = null)
    {
        if (!session()->has('currency_format_setting' . $currencyId)) {
            $setting = $currencyId == null ? Currency::first() : Currency::where('id', $currencyId)->first();
            session(['currency_format_setting' . $currencyId => $setting]);
        }

        return session('currency_format_setting' . $currencyId);
    }

}

if (!function_exists('currency_format')) {

    // @codingStandardsIgnoreLine
    function currency_format($amount, $currencyId = null, $showSymbol = true)
    {
        $formats = currency_format_setting($currencyId);

        if (!$showSymbol) {
            $currency_symbol = '';
        }
        else {
            $settings = $formats->company ?? Company::find($formats->company_id);
            $currency_symbol = $currencyId == null ? $settings->currency->currency_symbol : $formats->currency_symbol;
        }

        $currency_position = $formats->currency_position;
        $no_of_decimal = !is_null($formats->no_of_decimal) ? $formats->no_of_decimal : '0';
        $thousand_separator = !is_null($formats->thousand_separator) ? $formats->thousand_separator : '';
        $decimal_separator = !is_null($formats->decimal_separator) ? $formats->decimal_separator : '0';

        $amount = number_format($amount, $no_of_decimal, $decimal_separator, $thousand_separator);

        $amount = match ($currency_position) {
            'right' => $amount . $currency_symbol,
            'left_with_space' => $currency_symbol . ' ' . $amount,
            'right_with_space' => $amount . ' ' . $currency_symbol,
            default => $currency_symbol . $amount,
        };

        return $amount;
    }

}

if (!function_exists('attendance_setting')) {

    // @codingStandardsIgnoreLine
    function attendance_setting()
    {
        if (!session()->has('attendance_setting')) {
            session(['attendance_setting' => AttendanceSetting::first()]);
        }

        return session('attendance_setting');
    }

}

if (!function_exists('add_project_permission')) {

    // @codingStandardsIgnoreLine
    function add_project_permission()
    {
        if (!session()->has('add_project_permission') && user()) {
            session(['add_project_permission' => user()->permission('add_projects')]);
        }

        return session('add_project_permission');
    }

}

if (!function_exists('add_tasks_permission')) {

    // @codingStandardsIgnoreLine
    function add_tasks_permission()
    {
        if (!session()->has('add_tasks_permission') && user()) {
            session(['add_tasks_permission' => user()->permission('add_tasks')]);
        }

        return session('add_tasks_permission');
    }

}

if (!function_exists('add_clients_permission')) {

    // @codingStandardsIgnoreLine
    function add_clients_permission()
    {
        if (!session()->has('add_clients_permission') && user()) {
            session(['add_clients_permission' => user()->permission('add_clients')]);
        }

        return session('add_clients_permission');
    }

}

if (!function_exists('add_employees_permission')) {

    // @codingStandardsIgnoreLine
    function add_employees_permission()
    {
        if (!session()->has('add_employees_permission') && user()) {
            session(['add_employees_permission' => user()->permission('add_employees')]);
        }

        return session('add_employees_permission');
    }

    // @codingStandardsIgnoreLine

}

if (!function_exists('add_payments_permission')) {

    // @codingStandardsIgnoreLine
    function add_payments_permission()
    {
        if (!session()->has('add_payments_permission') && user()) {
            session(['add_payments_permission' => user()->permission('add_payments')]);
        }

        return session('add_payments_permission');
    }

    // @codingStandardsIgnoreLine
}

if (!function_exists('add_tickets_permission')) {

    // @codingStandardsIgnoreLine
    function add_tickets_permission()
    {
        if (!session()->has('add_tickets_permission') && user()) {
            session(['add_tickets_permission' => user()->permission('add_tickets')]);
        }

        return session('add_tickets_permission');
    }

}

if (!function_exists('add_timelogs_permission')) {

    // @codingStandardsIgnoreLine
    function add_timelogs_permission()
    {
        if (!session()->has('add_timelogs_permission') && user()) {
            session(['add_timelogs_permission' => user()->permission('add_timelogs')]);
        }

        return session('add_timelogs_permission');
    }

}

if (!function_exists('manage_active_timelogs')) {

    // @codingStandardsIgnoreLine
    function manage_active_timelogs()
    {
        if (!session()->has('manage_active_timelogs') && user()) {
            session(['manage_active_timelogs' => user()->permission('manage_active_timelogs')]);
        }

        return session('manage_active_timelogs');
    }

}

if (!function_exists('slack_setting')) {

    // @codingStandardsIgnoreLine
    function slack_setting()
    {
        if (!session()->has('slack_setting')) {
            session(['slack_setting' => \App\Models\SlackSetting::first()]);
        }

        return session('slack_setting');
    }

}

if (!function_exists('default_address')) {

    // @codingStandardsIgnoreLine
    function default_address()
    {
        if (!session()->has('default_address')) {
            session(['default_address' => company()->defaultAddress]);
        }

        return session('default_address');
    }

}

if (!function_exists('abort_403')) {

    // @codingStandardsIgnoreLine
    function abort_403($condition)
    {
        abort_if($condition, 403, __('messages.permissionDenied'));
    }

}

if (!function_exists('sidebar_user_perms')) {

    // @codingStandardsIgnoreLine
    function sidebar_user_perms()
    {
        if (!cache()->has('sidebar_user_perms_' . user()->id)) {

            $sidebarPermissionsArray = [
                'view_clients',
                'view_lead',
                'view_employees',
                'view_leave',
                'view_attendance',
                'view_holiday',
                'view_contract',
                'view_projects',
                'view_tasks',
                'view_timelogs',
                'view_estimates',
                'view_invoices',
                'view_payments',
                'view_expenses',
                'view_product',
                'view_order',
                'view_tickets',
                'view_events',
                'view_notice',
                'view_task_report',
                'view_time_log_report',
                'view_finance_report',
                'view_income_expense_report',
                'view_leave_report',
                'view_lead_proposals',
                'view_attendance_report',
                'manage_company_setting',
                'add_employees',
                'view_knowledgebase',
                'view_shift_roster',
                'view_designation',
                'view_department',
                'view_overview_dashboard',
                'view_project_dashboard',
                'view_client_dashboard',
                'view_hr_dashboard',
                'view_ticket_dashboard',
                'view_finance_dashboard',
                'view_expense_report',
                'view_client_note',
                'view_bankaccount',
                'view_appreciation',
                'manage_award',
                'view_lead_report',
                'view_sales_report',
                'view_deals',
            ];


            $sidebarPermissions = Permission::whereIn('name', $sidebarPermissionsArray)->select('id', 'name')->orderBy('id', 'asc')->get();

            $sidebarPermissionsId = $sidebarPermissions->pluck('id')->toArray();

            $sidebarUserPermissionType = UserPermission::where('user_id', user()->id)
                ->whereIn('permission_id', $sidebarPermissionsId)
                ->join('permissions', 'permissions.id', '=', 'user_permissions.permission_id')
                //                ->orderBy('user_permissions.id')
                ->select('user_permissions.permission_type_id', 'permissions.name', 'permissions.id')
                ->groupBy(['user_id', 'permission_id', 'permission_type_id'])
                ->get()
                ->keyBy('name');

            $sidebarUserPermissions = array_combine($sidebarUserPermissionType->pluck('name')->toArray(), $sidebarUserPermissionType->pluck('permission_type_id')->toArray());

            $unassignedPermissions = array_diff($sidebarPermissionsId, $sidebarUserPermissionType->pluck('id')->toArray());

            $filteredPermissions = $sidebarPermissions->filter(function ($item) use ($unassignedPermissions) {
                return in_array($item->id, $unassignedPermissions);
            });

            foreach ($filteredPermissions as $item) {
                $sidebarUserPermissions[$item->name] = 5;
            }

            cache(['sidebar_user_perms_' . user()->id => $sidebarUserPermissions]);
        }

        return cache('sidebar_user_perms_' . user()->id);

    }

}

if (!function_exists('mb_ucfirst')) {

    // @codingStandardsIgnoreLine
    function mb_ucfirst($string, $encoding = 'utf8')
    {
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, null, $encoding);

        return mb_strtoupper($firstChar, $encoding) . $then;
    }

}

if (!function_exists('mb_ucwords')) {

    // @codingStandardsIgnoreLine
    function mb_ucwords($string, $encoding = 'utf8')
    {
        return mb_convert_case($string, MB_CASE_TITLE, $encoding);
    }

}

if (!function_exists('minute_to_hour')) {

    // @codingStandardsIgnoreLine
    function minute_to_hour($totalMinutes)
    {
        return \Carbon\CarbonInterval::formatHuman($totalMinutes);
        /** @phpstan-ignore-line */
    }

}

if (!function_exists('can_upload')) {

    // @codingStandardsIgnoreLine
    function can_upload($size = 0)
    {
        if (!session()->has('client_company')) {
            session()->forget(['company_setting', 'company']);
        }

        // Return true for unlimited file storage
        if (company()->package->max_storage_size == -1) {
            return true;
        }

        // Total Space in package in MB
        $totalSpace = (company()->package->storage_unit == 'mb') ? company()->package->max_storage_size : company()->package->max_storage_size * 1024;

        // Used space in mb
        $fileStorage = \App\Models\FileStorage::all();
        $usedSpace = $fileStorage->count() > 0 ? round($fileStorage->sum('size') / (1000 * 1024), 4) : 0;

        $remainingSpace = $totalSpace - $usedSpace;

        if ($usedSpace > $totalSpace || $size > $remainingSpace) {
            return false;
        }

        return true;
    }

}

if (!function_exists('isWorksuiteSaas')) {

    function isWorksuiteSaas()
    {
        return strtolower(config('app.app_name')) === 'worksuite-saas';
    }

}

if (!function_exists('isWorksuite')) {

    function isWorksuite()
    {
        return strtolower(config('app.app_name')) === 'worksuite';
    }

}

if (!function_exists('showId')) {

    function showId()
    {
        return isWorksuite();
    }

}

if (!function_exists('getDomainSpecificUrl')) {

    function getDomainSpecificUrl($url, $company = null)
    {
        // Check if Subdomain module exist
        if (!module_enabled('Subdomain')) {
            return $url;
        }

        config(['app.url' => config('app.main_app_url')]);

        // If company specific
        if ($company) {
            $companyUrl = (config('app.redirect_https') ? 'https' : 'http') . '://' . $company->sub_domain;

            config(['app.url' => $companyUrl]);
            // Removed Illuminate\Support\Facades\URL::forceRootUrl($companyUrl);

            $url = str_replace(request()->getHost(), $company->sub_domain, $url);
            $url = str_replace('www.', '', $url);

            // Replace https to http for sub-domain to
            if (!config('app.redirect_https')) {
                return str_replace('https', 'http', $url);
            }

            return $url;
        }

        // Removed config(['app.url' => $url]);
        // Comment      \Illuminate\Support\Facades\URL::forceRootUrl($url);
        // If there is no company and url has login means
        // New superadmin is created
        return str_replace('login', 'super-admin-login', $url);
    }

}

if (!function_exists('getSubdomainSchema')) {

    function getSubdomainSchema()
    {

        if (!session()->has('subdomain_schema')) {
            if (\Illuminate\Support\Facades\Schema::hasTable('sub_domain_module_settings')) {
                $data = \Illuminate\Support\Facades\DB::table('sub_domain_module_settings')->first();
            }

            session(['subdomain_schema' => isset($data->schema) ? $data->schema : 'http']);
        }

        return session('subdomain_schema');
    }

}

if (!function_exists('getDomain')) {

    function getDomain($host = false)
    {
        if (!$host) {
            $host = $_SERVER['SERVER_NAME'] ?? 'worksuite-saas.test';
        }

        $shortDomain = config('app.short_domain_name');
        $dotCount = ($shortDomain === true) ? 2 : 1;

        $myHost = strtolower(trim($host));
        $count = substr_count($myHost, '.');

        if ($count === $dotCount || $count === 1) {
            return $myHost;
        }

        $myHost = explode('.', $myHost, 2);

        return end($myHost);
    }

}

if (!function_exists('company')) {

    function company()
    {

        if (session()->has('company')) {
            return session('company');
        }


        if (user()) {

            if (user()->company) {
                $company = user()->company;
                session(['company' => $company]);

                return $company;
            }

            return session('company');
        }

        return false;
    }

}

if (!function_exists('companyOrGlobalSetting')) {

    function companyOrGlobalSetting()
    {
        if (session()->has('companyOrGlobalSetting')) {
            return session('companyOrGlobalSetting');
        }


        if (user()) {

            if (user()->company) {
                $company = user()->company;
                session(['companyOrGlobalSetting' => $company]);

                return $company;
            }

            return global_setting();
        }

        return global_setting();
    }

}

if (!function_exists('superadmin_theme')) {

    // @codingStandardsIgnoreLine
    function superadmin_theme()
    {
        if (!session()->has('superadmin_theme')) {
            session(['superadmin_theme' => ThemeSetting::withoutGlobalScope(CompanyScope::class)->where('panel', 'superadmin')->first()]);
        }

        return session('superadmin_theme');
    }

}

if (!function_exists('trim_editor')) {

    // @codingStandardsIgnoreLine
    function trim_editor($text)
    {
        $search = '/' . preg_quote('<p><br></p>', '/') . '/';

        return preg_replace($search, '', trim($text), 1);
    }

}

if (!function_exists('quickbooks_setting')) {

    // @codingStandardsIgnoreLine
    function quickbooks_setting()
    {
        if (!session()->has('quickbooks_setting')) {
            $qbSetting = QuickBooksSetting::first();
            session(['quickbooks_setting' => $qbSetting]);

            return $qbSetting;
        }

        return session('quickbooks_setting');
    }

    // @codingStandardsIgnoreLine

}

if (!function_exists('user_role_ids')) {

    /**
     * Return current logged in user
     */
    // @codingStandardsIgnoreLine
    function user_role_ids()
    {
        if (session()->has('user_role_ids')) {
            return session('user_role_ids');
        }

        return null;
    }

}

if (!function_exists('canDataTableExport')) {

    function canDataTableExport()
    {
        return in_array('admin', user_roles()) || (company()->employee_can_export_data && in_array('employee', user_roles()));
    }

}

if (!function_exists('pdfStripTags')) {

    function pdfStripTags($text)
    {
        return strip_tags($text, [
            'p',
            'b',
            'strong',
            'a',
            'ul',
            'li',
            'ol',
            'i',
            'u',
            'blockquote',
            'img',
        ]);
    }

}

if (!function_exists('companyToYmd')) {

    function companyToYmd($date)
    {
        return Carbon::createFromFormat(company()->date_format, $date)->format('Y-m-d');
    }

}

if (!function_exists('companyToDateString')) {

    function companyToDateString($date)
    {
        return Carbon::createFromFormat(company()->date_format, $date)->toDateString();
    }

}

if (!function_exists('custom_link_setting')) {

    // @codingStandardsIgnoreLine
    function custom_link_setting()
    {
        if (!session()->has('custom_link_setting')) {
            session(['custom_link_setting' => CustomLinkSetting::all()]);
        }

        return session('custom_link_setting');
    }

}

if (!function_exists('isRtl')) {

    // @codingStandardsIgnoreLine
    function isRtl($class = null)
    {
        if (!session()->has('isRtl')) {

            $rtl = false;

            if (user()) {
                $locale = user()->locale;
            }
            else {
                $locale = global_setting()->locale;
            }

            if ($locale) {
                $userLanguage = LanguageSetting::where('language_code', $locale)->first();

                if ($userLanguage) {
                    $rtl = $userLanguage->is_rtl;
                }
            }

            session(['isRtl' => $rtl]);
        }

        $isRtl = session('isRtl');

        return is_null($class) ? $isRtl : ($isRtl ? $class : false);
    }

}
