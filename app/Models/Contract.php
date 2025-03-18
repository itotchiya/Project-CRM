<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Contract
 *
 * @property int $id
 * @property int $client_id
 * @property string $subject
 * @property string $amount
 * @property string $original_amount
 * @property int|null $contract_type_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property string $original_start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string|null $original_end_date
 * @property string|null $description
 * @property string|null $contract_name
 * @property string|null $company_logo
 * @property string|null $alternate_address
 * @property string|null $cell
 * @property string|null $office
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $postal_code
 * @property string|null $contract_detail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User $client
 * @property-read \App\Models\ContractType|null $contractType
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContractDiscussion[] $discussion
 * @property-read int|null $discussion_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContractFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $icon
 * @property-read mixed $image_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContractRenew[] $renewHistory
 * @property-read int|null $renew_history_count
 * @property-read \App\Models\ContractSign|null $signature
 * @method static \Database\Factories\ContractFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereAlternateAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCompanyLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOriginalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOriginalEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOriginalStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedAt($value)
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereHash($value)
 * @property int|null $currency_id
 * @property-read \App\Models\Currency|null $currency
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCurrencyId($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEventId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCompanyId($value)
 * @property int|null $contract_number
 * @property int|null $project_id
 * @property string|null $contract_note
 * @property-read mixed $extras
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereProjectId($value)
 * @property string|null $company_sign
 * @property string|null $sign_date
 * @property-read mixed $company_signature
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCompanySign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereSignDate($value)
 * @property string|null $original_contract_number
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOriginalContractNumber($value)
 * @mixin \Eloquent
 */
class Contract extends BaseModel
{

    use CustomFieldsTrait, HasFactory, HasCompany;

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'sign_date' => 'datetime',
    ];

    protected $with = [];

    protected $appends = ['image_url', 'company_signature'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Contract';

    public function signer()
    {
        return $this->belongsTo(User::class, 'sign_by');
    }

    public function getImageUrlAttribute()
    {
        return ($this->company_logo) ? asset_url_local_s3('contract-logo/' . $this->company_logo) : $this->company->logo_url;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function signature(): HasOne
    {
        return $this->hasOne(ContractSign::class, 'contract_id');
    }

    public function discussion(): HasMany
    {
        return $this->hasMany(ContractDiscussion::class)->orderByDesc('id');
    }

    public function renewHistory(): HasMany
    {
        return $this->hasMany(ContractRenew::class, 'contract_id')->orderByDesc('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ContractFile::class, 'contract_id')->orderByDesc('id');
    }

    public static function lastContractNumber()
    {
        return (int)Contract::latest()->first()?->original_contract_number ?? 0;
    }

    public function formatContractNumber()
    {
        $invoiceSettings = company() ? company()->invoiceSetting : $this->company->invoiceSetting;
        return \App\Helper\NumberFormat::contract($this->contract_number, $invoiceSettings);
    }

    public function getCompanySignatureAttribute()
    {
        return asset_url_local_s3('contract/sign/' . $this->company_sign);
    }

}
