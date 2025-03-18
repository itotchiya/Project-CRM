<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\EventFile
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $event_id
 * @property string|null $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EventFile extends BaseModel
{

    use IconTrait;

    use HasFactory;

    const FILE_PATH = 'events';

    protected $appends = ['file_url', 'icon'];

    public function getFileUrlAttribute()
    {
        return asset_url_local_s3(EventFile::FILE_PATH . '/' . $this->event_id . '/' . $this->hashname);
    }

}
