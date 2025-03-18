<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\DiscussionCategory
 *
 * @property int $id
 * @property int $order
 * @property string $name
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
class DiscussionCategory extends BaseModel
{

    use HasCompany;

    protected $guarded = ['id'];

}
