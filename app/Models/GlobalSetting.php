<?php

namespace App\Models;

use App\Traits\HasMaskImage;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Company
 *
 * @property int $id
 * @property string $global_app_name
 * @property string $currency_key_version
 * @property string $license_type
 * @property string|null $logo
 * @property string|null $email
 * @property string|null $login_background
 * @property string $address
 * @property string|null $website
 * @property int|null $currency_id
 * @property int $rtl
 * @property int $show_update_popup
 * @property string $timezone
 * @property string $date_format
 * @property string|null $date_picker_format
 * @property string|null $moment_format
 * @property string $time_format
 * @property string $locale
 * @property string $latitude
 * @property string $longitude
 * @property string $leaves_start_from
 * @property string $active_theme
 * @property int|null $last_updated_by
 * @property string|null $currency_converter_key
 * @property string|null $google_map_key
 * @property string $task_self
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $purchase_code
 * @property string|null $supported_until
 * @property string $google_recaptcha_status
 * @property string $google_recaptcha_v2_status
 * @property string|null $google_recaptcha_v2_site_key
 * @property string|null $google_recaptcha_v2_secret_key
 * @property string $google_recaptcha_v3_status
 * @property string|null $google_recaptcha_v3_site_key
 * @property string|null $google_recaptcha_v3_secret_key
 * @property string|null $moment_format
 * @property int $app_debug
 * @property int $rounded_theme
 * @property int $hide_cron_message
 * @property int $system_update
 * @property string $logo_background_color
 * @property int $before_days
 * @property int $after_days
 * @property string $on_deadline
 * @property int $default_task_status
 * @property int $show_review_modal
 * @property int $dashboard_clock
 * @property int $taskboard_length
 * @property string|null $favicon
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $dark_logo_url
 * @property-read mixed $favicon_url
 * @property-read mixed $icon
 * @property-read mixed $light_logo_url
 * @property-read mixed $masked_default_logo
 * @property-read mixed $login_background_url
 * @property-read mixed $logo_url
 * @property-read mixed $moment_date_format
 * @property-read mixed $show_public_message
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereActiveTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAfterDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAppDebug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereBeforeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCurrencyConverterKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDashboardClock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDatePickerFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDefaultTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleMapKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2SiteKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2Status($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3SiteKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3Status($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereHideCronMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLeavesStartFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLoginBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLogoBackgroundColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereMomentFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereOnDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting wherePurchaseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereRoundedTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereShowReviewModal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSupportedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSystemUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTaskSelf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTaskboardLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTimeFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereWeatherKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereWebsite($value)
 * @property int $ticket_form_google_captcha
 * @property int $lead_form_google_captcha
 * @property string|null $last_cron_run
 * @property string $auth_theme
 * @property string|null $light_logo
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAuthTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLastCronRun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLeadFormGoogleCaptcha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLightLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTicketFormGoogleCaptcha($value)
 * @property string $sidebar_logo_style
 * @property string $session_driver
 * @property int $allow_client_signup
 * @property int $admin_client_signup_approval
 * @property string|null $allowed_file_types
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAdminClientSignupApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowClientSignup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowedFileTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSessionDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSidebarLogoStyle($value)
 * @property string $google_calendar_status
 * @property string|null $google_client_id
 * @property string|null $google_client_secret
 * @property string $google_calendar_verification_status
 * @property string|null $google_id
 * @property string|null $name
 * @property string|null $token
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleCalendarStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleCalendarVerificationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereToken($value)
 * @property int $allowed_file_size
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowedFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereCurrencyKeyVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereGlobalAppName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereLicenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereMomentDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereRtl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereShowUpdatePopup($value)
 * @property string $header_color
 * @property string|null $hash
 * @property string|null $last_license_verified_at
 * @property int $datatable_row_limit
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereDatatableRowLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereHeaderColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereLastLicenseVerifiedAt($value)
 * @property string $auth_theme_text
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereAuthThemeText($value)
 * @property string $sign_up_terms
 * @property string|null $terms_link
 * @property int $allow_max_no_of_files
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereAllowMaxNoOfFiles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereSignUpTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereTermsLink($value)
 * @property string|null $purchased_on
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting wherePurchasedOn($value)
 * @mixin \Eloquent
 */
class GlobalSetting extends BaseModel
{

    use HasMaskImage;

    const CHECKLIST_TOTAL = 6;
    const SIGNED_ROUTE_EXPIRY = 7; // Days

    const APP_LOGO_PATH = 'app-logo';

    protected $casts = [
        'google_map_key' => 'encrypted',
        'google_client_secret' => 'encrypted'
    ];

    public $dates = ['last_cron_run'];

    const COMPANY_TABLES = [
        'accept_estimates',
        'attendance_settings',
        'attendances',
        'client_categories',
        'client_contacts',
        'client_details',
        'client_docs',
        'client_notes',
        'client_sub_categories',
        'client_user_notes',
        'company_addresses',
        'contract_discussions',
        'contract_files',
        'contract_renews',
        'contract_signs',
        'contract_templates',
        'contract_types',
        'contracts',
        'conversation',
        'conversation_reply',
        'credit_notes',
        'currencies',
        'currency_format_settings',
        'custom_field_groups',
        'custom_fields',
        'dashboard_widgets',
        'designations',
        'discussion_categories',
        'discussion_files',
        'discussion_replies',
        'discussions',
        'email_notification_settings',
        'emergency_contacts',
        'employee_details',
        'employee_docs',
        'employee_shifts',
        'employee_shift_change_requests',
        'employee_skills',
        'employee_teams',
        'estimates',
        'event_attendees',
        'event_files',
        'events',
        'expenses',
        'expenses_category',
        'expenses_category_roles',
        'expenses_recurring',
        'google_calendar_modules',
        'holidays',
        'invoice_recurring',
        'invoice_settings',
        'invoices',
        'issues',
        'knowledge_base_files',
        'knowledge_bases',
        'knowledge_categories',
        'lead_agents',
        'lead_category',
        'lead_custom_forms',
        'lead_sources',
        'lead_status',
        'leads',
        'leave_types',
        'leaves',
        'log_time_for',
        'message_settings',
        'module_settings',
        'notice_views',
        'notices',
        'offline_payment_methods',
        'orders',
        'payment_gateway_credentials',
        'payments',
        'pinned',
        'product_category',
        'product_files',
        'product_sub_category',
        'products',
        'project_category',
        'project_settings',
        'project_status_settings',
        'project_templates',
        'project_time_logs',
        'project_time_log_breaks',
        'projects',
        'proposal_template_item_images',
        'proposal_template_items',
        'proposal_templates',
        'proposals',
        'push_subscriptions',
        'quotations',
        'removal_requests',
        'removal_requests_lead',
        'roles',
        'skills',
        'slack_settings',
        'sticky_notes',
        'task_category',
        'task_settings',
        'taskboard_columns',
        'tasks',
        'taxes',
        'teams',
        'theme_settings',
        'ticket_agent_groups',
        'ticket_channels',
        'ticket_custom_forms',
        'ticket_email_settings',
        'ticket_groups',
        'ticket_reply_templates',
        'ticket_tag_list',
        'ticket_tags',
        'ticket_types',
        'tickets',
        'universal_search',
        'user_activities',
        'user_invitations',
        'user_leadboard_settings',
        'user_taskboard_settings',
        'users',
        'users_chat',
        'users_chat_files',
        'file_storage',
        'task_label_list'
    ];

    const CURRENCY_TABLES = [
        'companies',
        'contracts',
        'contract_templates',
        'credit_notes',
        'estimates',
        'expenses',
        'expenses_recurring',
        'leads',
        'invoices',
        'invoice_recurring',
        'orders',
        'payments',
        'proposals',
        'proposal_templates',
        'projects',
        'project_milestones',
    ];

    protected $appends = [
        'logo_url',
        'login_background_url',
        'show_public_message',
        'moment_date_format',
        'favicon_url'
    ];

    const DATE_FORMATS = [
        'd-m-Y' => 'DD-MM-YYYY',
        'm-d-Y' => 'MM-DD-YYYY',
        'Y-m-d' => 'YYYY-MM-DD',
        'd.m.Y' => 'DD.MM.YYYY',
        'm.d.Y' => 'MM.DD.YYYY',
        'Y.m.d' => 'YYYY.MM.DD',
        'd/m/Y' => 'DD/MM/YYYY',
        'm/d/Y' => 'MM/DD/YYYY',
        'Y/m/d' => 'YYYY/MM/DD',
        'd/M/Y' => 'DD/MMM/YYYY',
        'd.M.Y' => 'DD.MMM.YYYY',
        'd-M-Y' => 'DD-MMM-YYYY',
        'd M Y' => 'DD MMM YYYY',
        'd F, Y' => 'DD MMMM, YYYY',
        'd D M Y' => 'DD ddd MMM YYYY',
        'D d M Y' => 'ddd DD MMM YYYY',
        'dS M Y' => 'Do MMM YYYY',
    ];

    const SELECT2_SHOW_COUNT = 20;

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function getLogoUrlAttribute()
    {
        if (user()) {
            if (user()->dark_theme) {
                return $this->defaultLogo();
            }
        }

        if (company() && company()->auth_theme == 'dark') {
            return $this->defaultLogo();
        }

        if (is_null($this->light_logo)) {
            return asset('img/worksuite-logo.png');
        }

        return asset_url_local_s3('app-logo/' . $this->light_logo);

    }

    public function defaultLogo()
    {
        if (is_null($this->logo)) {
            return asset('img/worksuite-logo.png');
        }

        return asset_url_local_s3('app-logo/' . $this->logo);
    }

    public function getLightLogoUrlAttribute()
    {
        if (is_null($this->light_logo)) {
            return asset('img/worksuite-logo.png');
        }

        return asset_url_local_s3('app-logo/' . $this->light_logo);
    }

    public function getDarkLogoUrlAttribute()
    {
        if (is_null($this->logo)) {
            return asset('img/worksuite-logo.png');
        }

        return asset_url_local_s3('app-logo/' . $this->logo);
    }

    public function getLoginBackgroundUrlAttribute()
    {

        if (is_null($this->login_background) || $this->login_background == 'login-background.jpg') {
            return null;
        }

        return asset_url_local_s3('login-background/' . $this->login_background);
    }

    public function maskedDefaultLogo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->logo)) {
                    return asset('img/worksuite-logo.png');
                }

                return $this->generateMaskedImageAppUrl('app-logo/' . $this->logo);
            },
        );

    }

    public function maskedLogoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (user()) {
                    if (user()->dark_theme) {
                        return $this->masked_default_logo;
                    }
                }

                if (company() && company()->auth_theme == 'dark') {
                    return $this->masked_default_logo;
                }

                if (is_null($this->light_logo)) {
                    return asset('img/worksuite-logo.png');
                }

                return $this->generateMaskedImageAppUrl('app-logo/' . $this->light_logo);
            },
        );
    }

    public function maskedLightLogoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->light_logo)) {
                    return asset('img/worksuite-logo.png');
                }

                return $this->generateMaskedImageAppUrl('app-logo/' . $this->light_logo);
            },
        );

    }

    public function maskedDarkLogoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->logo)) {
                    return asset('img/worksuite-logo.png');
                }

                return $this->generateMaskedImageAppUrl('app-logo/' . $this->logo);
            },
        );

    }

    public function maskedLoginBackgroundUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->login_background) || $this->login_background == 'login-background.jpg') {
                    return null;
                }

                return $this->generateMaskedImageAppUrl('login-background/' . $this->login_background);
            },
        );

    }

    public function maskedFaviconUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->favicon)) {
                    return asset('favicon.png');
                }

                return $this->generateMaskedImageAppUrl('favicon/' . $this->favicon);
            },
        );

    }

    public function getShowPublicMessageAttribute()
    {
        if (str_contains(request()->url(), request()->getHost() . '/public')) {
            return true;
        }

        return false;
    }

    public function getMomentDateFormatAttribute()
    {

        return isset($this->date_format) ? self::DATE_FORMATS[$this->date_format] : null;
    }

    public function getFaviconUrlAttribute()
    {
        if (is_null($this->favicon)) {
            return asset('favicon.png');
        }

        return asset_url_local_s3('favicon/' . $this->favicon);
    }

    public static function checkListCompleted()
    {
        $checkListCompleted = 2; // Installation and Admin Account setup

        if (smtp_setting()->mail_from_email != 'from@email.com') {
            $checkListCompleted++;
        }

        if (!is_null(global_setting()->last_cron_run)) {
            $checkListCompleted++;
        }

        if (!is_null(global_setting()->logo)) {
            $checkListCompleted++;
        }

        if (!is_null(global_setting()->favicon)) {
            $checkListCompleted++;
        }

        return $checkListCompleted;
    }

    public static function getMonthsOfYear($full = 'F')
    {
        $months = [];

        for ($monthNumber = 1; $monthNumber <= Carbon::MONTHS_PER_YEAR; $monthNumber++) {
            $monthName = Carbon::create(null, $monthNumber)->translatedFormat($full);
            $months[] = $monthName;
        }

        return $months;
    }

    public static function getDaysOfWeek($full = 'D')
    {
        $days = [];

        for ($dayNumber = 0; $dayNumber < Carbon::DAYS_PER_WEEK; $dayNumber++) {
            $dayName = now()->startOfWeek(0)->addDays($dayNumber)->translatedFormat($full);
            $days[] = $dayName;
        }

        return $days;
    }

    public static function validateGoogleRecaptcha($googleRecaptchaResponse)
    {
        $secretKey = global_setting()->google_recaptcha_v2_status == 'active' ? global_setting()->google_recaptcha_v2_secret_key : global_setting()->google_recaptcha_v3_secret_key;

        $client = new Client();
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'form_params' => [
                'secret' => $secretKey,
                'response' => $googleRecaptchaResponse,
                'remoteip' => $_SERVER['REMOTE_ADDR'],
            ],
        ]);

        $body = json_decode($response->getBody());

        return $body->success;
    }

}
