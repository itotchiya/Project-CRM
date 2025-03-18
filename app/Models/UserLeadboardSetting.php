<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\UserLeadboardSetting
 *
 * @property int $id
 * @property int $user_id
 * @property int $board_column_id
 * @property int $collapsed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereBoardColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereCollapsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereCompanyId($value)
 * @property int|null $pipeline_stage_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting wherePipelineStageId($value)
 * @mixin \Eloquent
 */
class UserLeadboardSetting extends BaseModel
{

    use HasFactory;
    use HasCompany;

    protected $guarded = ['id'];

}
