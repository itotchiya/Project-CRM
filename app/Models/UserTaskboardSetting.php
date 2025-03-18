<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\UserTaskboardSetting
 *
 * @property int $id
 * @property int $user_id
 * @property int $board_column_id
 * @property int $collapsed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereBoardColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereCollapsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereCompanyId($value)
 * @mixin \Eloquent
 */
class UserTaskboardSetting extends BaseModel
{

    use HasFactory, HasCompany;

    protected $guarded = ['id'];

}
