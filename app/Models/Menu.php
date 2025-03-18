<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\Menu
 *
 * @property int $id
 * @property string $menu_name
 * @property string|null $translate_name
 * @property string|null $route
 * @property string|null $module
 * @property string|null $icon
 * @property int|null $setting_menu
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereMenuName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSettingMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereTranslateName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCompanyId($value)
 * @mixin \Eloquent
 */
class Menu extends BaseModel
{

    use HasCompany;

    protected $guarded = ['id'];

}
