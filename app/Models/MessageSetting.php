<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\MessageSetting
 *
 * @property int $id
 * @property string $allow_client_admin
 * @property string $allow_client_employee
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereAllowClientAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereAllowClientEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereUpdatedAt($value)
 * @property int|null $company_id
 * @property string $restrict_client
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereRestrictClient($value)
 * @property int $send_sound_notification
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereSendSoundNotification($value)
 * @mixin \Eloquent
 */
class MessageSetting extends BaseModel
{

    use HasCompany;

    //
}
