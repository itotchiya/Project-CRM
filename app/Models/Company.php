<?php

namespace App\Models;

use App\Traits\HasMaskImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;

/**
 * App\Models\Company
 *
 * @property int $id
 * @property string $company_name
 * @property string $app_name
 * @property string $company_email
 * @property string $company_phone
 * @property string|null $logo
 * @property string|null $login_background
 * @property string $address
 * @property string|null $website
 * @property int|null $currency_id
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
 * @property int $app_debug
 * @property int $rounded_theme
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
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowedFileSize($value)
 * @property string $status
 * @property string|null $last_login
 * @property int $rtl
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAppName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereRtl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereStatus($value)
 * @property-read \App\Models\AttendanceSetting|null $attendanceSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CompanyAddress[] $companyAddress
 * @property-read int|null $company_address_count
 * @property-read \App\Models\InvoiceSetting|null $invoiceSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeadAgent[] $leadAgents
 * @property-read int|null $lead_agents_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeadCategory[] $leadCategories
 * @property-read int|null $lead_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeadSource[] $leadSources
 * @property-read int|null $lead_sources_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeadStatus[] $leadStats
 * @property-read int|null $lead_stats_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeaveType[] $leaveTypes
 * @property-read int|null $leave_types_count
 * @property-read \App\Models\MessageSetting|null $messageSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OfflinePaymentMethod[] $offlinePaymentMethod
 * @property-read int|null $offline_payment_method_count
 * @property-read \App\Models\PaymentGatewayCredentials|null $paymentGatewayCredentials
 * @property-read \App\Models\ProjectSetting|null $projectSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectStatusSetting[] $projectStatusSettings
 * @property-read int|null $project_status_settings_count
 * @property-read \App\Models\TaskSetting|null $taskSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tax[] $taxes
 * @property-read int|null $taxes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketChannel[] $ticketChannels
 * @property-read int|null $ticket_channels_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketType[] $ticketTypes
 * @property-read int|null $ticket_types_count
 * @property-read \App\Models\ProjectTimeLog|null $timeLogSetting
 * @property string|null $hash
 * @property-read \App\Models\LeaveSetting|null $leaveSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ModuleSetting[] $moduleSetting
 * @property-read int|null $module_setting_count
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereHash($value)
 * @property string $year_starts_from
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Contract[] $contracts
 * @property-read int|null $contracts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Currency[] $currencies
 * @property-read int|null $currencies_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Estimate[] $estimates
 * @property-read int|null $estimates_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FileStorage[] $fileStorage
 * @property-read int|null $file_storage_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects
 * @property-read int|null $projects_count
 * @property-read \App\Models\SlackSetting|null $slackSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereYearStartsFrom($value)
 * @property string $header_color
 * @property int $datatable_row_limit
 * @property int $show_new_webhook_alert
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property-read \App\Models\CompanyAddress|null $defaultAddress
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereDatatableRowLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereHeaderColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePmLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereShowNewWebhookAlert($value)
 * @property string $auth_theme_text
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAuthThemeText($value)
 * @property int $employee_can_export_data
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereEmployeeCanExportData($value)
 * @mixin \Eloquent
 */
class Company extends BaseModel
{

    use HasFactory;
    use HasMaskImage;

    protected $table = 'companies';

    public $dates = ['last_login'];

    protected $casts = [
        'google_calendar_status' => 'string'
    ];
    protected $appends = [
        'logo_url',
        'login_background_url',
        'moment_date_format',
        'favicon_url'
    ];

    const DATE_FORMATS = GlobalSetting::DATE_FORMATS;

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
            return global_setting()->light_logo_url;
        }

        return asset_url_local_s3('app-logo/' . $this->light_logo);

    }

    public function defaultLogo()
    {
        if (is_null($this->logo)) {
            return global_setting()->dark_logo_url;
        }

        return asset_url_local_s3('app-logo/' . $this->logo);
    }

    public function getLightLogoUrlAttribute()
    {
        if (is_null($this->light_logo)) {
            return global_setting()->light_logo_url;
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
                    return global_setting()->dark_logo_url;
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
                    return global_setting()->light_logo_url;
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
                    return global_setting()->light_logo_url;
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
                    return global_setting()->favicon_url;
                }

                return $this->generateMaskedImageAppUrl('favicon/' . $this->favicon);
            },
        );

    }

    public function getMomentDateFormatAttribute()
    {
        return array_key_exists($this->date_format, self::DATE_FORMATS)
        ? self::DATE_FORMATS[$this->date_format]
        : null;

        // return isset($this->date_format) ? self::DATE_FORMATS[$this->date_format] : null;
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('companies.status', 'active');
    }

    public function getFaviconUrlAttribute()
    {
        if (is_null($this->favicon)) {
            return global_setting()->favicon_url;
        }

        return asset_url_local_s3('favicon/' . $this->favicon);
    }

    public function paymentGatewayCredentials(): HasOne
    {
        return $this->hasOne(PaymentGatewayCredentials::class);
    }

    public function invoiceSetting(): HasOne
    {
        return $this->hasOne(InvoiceSetting::class);
    }

    public function offlinePaymentMethod(): HasMany
    {
        return $this->hasMany(OfflinePaymentMethod::class);
    }

    public function leaveTypes()
    {
        return $this->hasMany(LeaveType::class);
    }

    public function companyAddress(): HasMany
    {
        return $this->hasMany(CompanyAddress::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(CompanyAddress::class)->where('is_default', 1);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function ticketChannels(): HasMany
    {
        return $this->hasMany(TicketChannel::class);
    }

    public function projectSetting(): HasOne
    {
        return $this->hasOne(ProjectSetting::class);
    }

    public function projectStatusSettings(): HasMany
    {
        return $this->HasMany(ProjectStatusSetting::class);
    }

    public function attendanceSetting(): HasOne
    {
        return $this->HasOne(AttendanceSetting::class);
    }

    public function messageSetting(): HasOne
    {
        return $this->HasOne(MessageSetting::class);
    }

    public function leadSources(): HasMany
    {
        return $this->HasMany(LeadSource::class);
    }

    public function leadStats(): HasMany
    {
        return $this->HasMany(LeadStatus::class);
    }

    public function leadAgents(): HasMany
    {
        return $this->HasMany(LeadAgent::class);
    }

    public function leadCategories(): HasMany
    {
        return $this->HasMany(LeadCategory::class);
    }

    public function moduleSetting(): HasMany
    {
        return $this->HasMany(ModuleSetting::class);
    }

    public function currencies(): HasMany
    {
        return $this->HasMany(Currency::class);
    }

    public function timeLogSetting(): HasOne
    {
        return $this->HasOne(ProjectTimeLog::class);
    }

    public function taskSetting(): HasOne
    {
        return $this->HasOne(TaskSetting::class);
    }

    public function leaveSetting(): HasOne
    {
        return $this->HasOne(LeaveSetting::class);
    }

    public function slackSetting(): HasOne
    {
        return $this->HasOne(SlackSetting::class);
    }

    public function fileStorage(): HasMany
    {
        return $this->hasMany(FileStorage::class);
    }

    public static function renameOrganisationTableToCompanyTable()
    {
        if (Schema::hasTable('organisation_settings')) {
            Schema::rename('organisation_settings', 'companies');
        }
    }

    public function clients(): HasMany
    {
        return $this->hasMany(User::class)->whereHas('ClientDetails');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

}
