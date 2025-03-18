<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\UniversalSearch
 *
 * @property int $id
 * @property int $searchable_id
 * @property string|null $module_type
 * @property string $title
 * @property string $route_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch query()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereModuleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereRouteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereSearchableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereCompanyId($value)
 * @mixin \Eloquent
 */
class UniversalSearch extends BaseModel
{

    use HasCompany;

    protected $table = 'universal_search';

}
