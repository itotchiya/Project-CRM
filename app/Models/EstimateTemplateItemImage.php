<?php

namespace App\Models;

use App\Traits\IconTrait;

/**
 * App\Models\EstimateTemplateItemImage
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $estimate_template_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereEstimateTemplateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereUpdatedAt($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
class EstimateTemplateItemImage extends BaseModel
{
    use IconTrait;

    const FILE_PATH = 'estimate-files';

    protected $appends = ['file_url', 'icon', 'file'];
    protected $fillable = ['estimate_template_item_id', 'filename', 'hashname', 'size', 'external_link'];

    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(EstimateTemplateItemImage::FILE_PATH . '/' . $this->estimate_template_item_id . '/' . $this->hashname);
    }

    public function getFileAttribute()
    {
        return $this->external_link ?: (EstimateTemplateItemImage::FILE_PATH . '/' . $this->estimate_template_item_id . '/' . $this->hashname);
    }

}
