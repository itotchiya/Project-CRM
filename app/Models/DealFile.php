<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Holiday
 *
 * @package App\Models
 * @property int $id
 * @property int $lead_id
 * @property int $user_id
 * @property string $filename
 * @property string $hashname
 * @property string $size
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $dropbox_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\Lead $lead
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereUserId($value)
 * @mixin \Eloquent
 */
class DealFile extends BaseModel
{

    use IconTrait;

    const FILE_PATH = 'lead-files';

    protected $fillable = [];

    protected $guarded = ['id'];

    protected $appends = ['file_url', 'icon'];

    public function getFileUrlAttribute()
    {
        return asset_url_local_s3(DealFile::FILE_PATH . '/' . $this->deal_id . '/' . $this->hashname);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

}
