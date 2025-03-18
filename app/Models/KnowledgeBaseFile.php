<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\KnowledgeBaseFile
 *
 * @property protected $appends
 * @property int $id
 * @property int|null $company_id
 * @property int $knowledge_base_id
 * @property string|null $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link_name
 * @property string|null $external_link
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereKnowledgeBaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KnowledgeBaseFile extends BaseModel
{

    use HasFactory;
    use IconTrait;

    const FILE_PATH = 'knowledgebase';

    protected $fillable = [];
    protected $guarded = ['id'];
    protected $appends = ['file_url', 'icon'];

    public function getFileUrlAttribute()
    {
        return (!is_null($this->external_link)) ? $this->external_link : asset_url_local_s3(KnowledgeBaseFile::FILE_PATH . '/' . $this->knowledge_base_id . '/' . $this->hashname);
    }

}
