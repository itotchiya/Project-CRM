<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\RemovalRequest
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int|null $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereCompanyId($value)
 * @mixin \Eloquent
 */
class RemovalRequest extends BaseModel
{

    use HasCompany;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
