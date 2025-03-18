<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\AwardIcon
 *
 * @property-read \App\Models\Award|null $award
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon query()
 * @property int $id
 * @property string $title
 * @property string $icon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AwardIcon extends BaseModel
{

    protected $fillable = ['company_id', 'title', 'icon'];

    public function award(): HasOne
    {
        return $this->hasOne(Award::class, 'icon');
    }

}
