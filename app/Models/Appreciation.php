<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Appreciation
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $award_id
 * @property int $award_to
 * @property \Illuminate\Support\Carbon $award_date
 * @property string|null $image
 * @property string|null $summary
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $addedBy
 * @property-read \App\Models\Award $award
 * @property-read \App\Models\User $awardTo
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $image_url
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereAwardDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereAwardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereAwardTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Appreciation extends BaseModel
{
    use HasCompany;

    protected $casts = [
        'award_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $appends = ['image_url'];
    protected $with = [];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset_url_local_s3('appreciation/' . $this->image) : '';
    }

    public function awardTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'award_to')->withoutGlobalScope(ActiveScope::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class);
    }

}
