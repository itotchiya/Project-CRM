<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\QuickBooksSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $sandbox_client_id
 * @property string $sandbox_client_secret
 * @property string $client_id
 * @property string $client_secret
 * @property string $access_token
 * @property string $refresh_token
 * @property string $realmid
 * @property string $sync_type
 * @property string $environment
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereRealmid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereSandboxClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereSandboxClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereSyncType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class QuickBooksSetting extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];
}
