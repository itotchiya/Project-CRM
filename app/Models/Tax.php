<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Tax
 *
 * @property int $id
 * @property string $tax_name
 * @property string $rate_percent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|Tax newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereRatePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereTaxName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereUpdatedAt($value)
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Query\Builder|Tax onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Tax withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Tax withoutTrashed()
 * @mixin \Eloquent
 */
class Tax extends BaseModel
{

    use HasCompany;
    use SoftDeletes;
}
