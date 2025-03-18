<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Award
 *
 * @property-read \App\Models\Appreciation|null $appreciation
 * @property-read \App\Models\AwardIcon|null $awardIcon
 * @property-read \App\Models\Company|null $company
 * @method static Builder|Award newModelQuery()
 * @method static Builder|Award newQuery()
 * @method static Builder|Award query()
 * @property int $id
 * @property int|null $company_id
 * @property string $title
 * @property int|null $award_icon_id
 * @property string|null $summary
 * @property string $status
 * @property string $color_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Appreciation[] $appreciations
 * @property-read int|null $appreciations_count
 * @method static Builder|Award whereAwardIconId($value)
 * @method static Builder|Award whereColorCode($value)
 * @method static Builder|Award whereCompanyId($value)
 * @method static Builder|Award whereCreatedAt($value)
 * @method static Builder|Award whereId($value)
 * @method static Builder|Award whereStatus($value)
 * @method static Builder|Award whereSummary($value)
 * @method static Builder|Award whereTitle($value)
 * @method static Builder|Award whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Award extends BaseModel
{

    use HasCompany;

    public function appreciation(): HasOne
    {
        return $this->hasOne(Appreciation::class);
    }

    public function appreciations(): HasMany
    {
        return $this->hasMany(Appreciation::class);
    }

    public function awardIcon(): BelongsTo
    {
        return $this->belongsTo(AwardIcon::class);
    }

}
