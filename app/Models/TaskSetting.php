<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\TaskSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $task_category
 * @property string $project
 * @property string $start_date
 * @property string $due_date
 * @property string $assigned_to
 * @property string $assigned_by
 * @property string $description
 * @property string $label
 * @property string $status
 * @property string $priority
 * @property string $make_private
 * @property string $time_estimate
 * @property string $hours_logged
 * @property string $custom_fields
 * @property string $copy_task_link
 * @property string $files
 * @property string $sub_task
 * @property string $comments
 * @property string $time_logs
 * @property string $notes
 * @property string $history
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereAssignedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereCopyTaskLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereCustomFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereFiles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereHoursLogged($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereMakePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereSubTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereTaskCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereTimeEstimate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereTimeLogs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereUpdatedAt($value)
 * @property-read \App\Models\Company|null $company
 * @mixin \Eloquent
 */
class TaskSetting extends BaseModel
{

    use HasFactory, HasCompany;
}
