<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\TaskLabelList
 *
 * @property int $id
 * @property string $label_name
 * @property string|null $color
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read mixed $label_color
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereLabelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereUpdatedAt($value)
 * @property int|null $project_id
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereCompanyId($value)
 * @mixin \Eloquent
 */
class TaskLabelList extends BaseModel
{

    use HasCompany;

    protected $table = 'task_label_list';

    protected $guarded = ['id'];
    public $appends = ['label_color'];

    public function getLabelColorAttribute()
    {
        return $this->color ?: '#3b0ae1';
    }

}
