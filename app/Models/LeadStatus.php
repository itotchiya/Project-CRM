<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\LeadStatus
 *
 * @property int $id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $priority
 * @property int $default
 * @property string $label_color
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereCompanyId($value)
 * @mixin \Eloquent
 */
class LeadStatus extends BaseModel
{

    use HasCompany;

    protected $table = 'lead_status';

    public function leads(): HasMany
    {
        return $this->hasMany(Deal::class, 'status_id')->orderBy('column_priority');
    }

    public function userSetting(): HasOne
    {
        return $this->hasOne(UserLeadboardSetting::class, 'pipeline_stage_id')->where('user_id', user()->id);
    }

}
