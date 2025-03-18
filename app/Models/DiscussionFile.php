<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\DiscussionFile
 *
 * @property string|null $external_link
 * @property int $id
 * @property int $user_id
 * @property int|null $discussion_id
 * @property int|null $discussion_reply_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Discussion|null $discussion
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereDiscussionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereDiscussionReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereCompanyId($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
class DiscussionFile extends BaseModel
{

    use IconTrait, HasCompany;

    const FILE_PATH = 'discussion-files';

    protected $appends = ['file_url', 'icon', 'file'];

    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(DiscussionFile::FILE_PATH . '/' . $this->hashname);
    }

    public function getFileAttribute()
    {
        return $this->external_link ?: (DiscussionFile::FILE_PATH . '/' . $this->hashname);
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class, 'discussion_id');
    }

}
