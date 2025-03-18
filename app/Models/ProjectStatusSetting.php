<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\ProjectStatusSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $status_name
 * @property string $color
 * @property string $status
 * @property string $default_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereDefaultStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereStatusName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProjectStatusSetting extends BaseModel
{

    use HasFactory, HasCompany;

    const ACTIVE = '1';
    const INACTIVE = '0';

    const COLUMNS = [
        ['status_name' => 'in progress', 'color' => '#00b5ff', 'status' => 'active', 'default_status' => self::ACTIVE],
        ['status_name' => 'not started', 'color' => '#616e80', 'status' => 'active', 'default_status' => self::INACTIVE],
        ['status_name' => 'on hold', 'color' => '#f5c308', 'status' => 'active', 'default_status' => self::INACTIVE],
        ['status_name' => 'canceled', 'color' => '#d21010', 'status' => 'active', 'default_status' => self::INACTIVE],
        ['status_name' => 'finished', 'color' => '#679c0d', 'status' => 'active', 'default_status' => self::INACTIVE]
    ];

    protected $fillable = ['status_name', 'color', 'status', 'default_status'];

}
