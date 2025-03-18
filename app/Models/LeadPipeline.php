<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\LeadCategory
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCompanyId($value)
 * @property string|null $name
 * @property string|null $slug
 * @property int $priority
 * @property string $label_color
 * @property int $default
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PipelineStage> $stages
 * @property-read int|null $stages_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereSlug($value)
 * @mixin \Eloquent
 */
class LeadPipeline extends BaseModel
{

    use HasCompany;

    protected $default = ['id', 'name'];
    protected $with = [];

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class, 'lead_pipeline_id', 'id')->orderBy('pipeline_stages.priority');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'lead_pipeline_id', 'id');
    }

}
