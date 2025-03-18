<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\PipelineStage
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
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage query()
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereCompanyId($value)
 * @property int|null $lead_pipeline_id
 * @property string|null $name
 * @property string|null $slug
 * @property-read \App\Models\LeadPipeline|null $pipeline
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereLeadPipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereSlug($value)
 * @mixin \Eloquent
 */
class PipelineStage extends BaseModel
{

    use HasCompany;

    protected $table = 'pipeline_stages';

    public function leads(): HasMany
    {
        return $this->hasMany(Deal::class, 'pipeline_stage_id')->orderBy('deals.column_priority');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'pipeline_stage_id')->orderBy('deals.column_priority');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(LeadPipeline::class, 'lead_pipeline_id');
    }

    public function userSetting(): HasOne
    {
        return $this->hasOne(UserLeadboardSetting::class, 'pipeline_stage_id')->where('user_id', user()->id);
    }

}
