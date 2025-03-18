<?php

namespace App\Models;

use App\Traits\IconTrait;

/**
 * App\Models\ProposalTemplateItemImage
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $proposal_template_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereProposalTemplateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereUpdatedAt($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
class ProposalTemplateItemImage extends BaseModel
{

    use IconTrait;

    const FILE_PATH = 'proposal-files';

    protected $appends = ['file_url', 'icon', 'file'];
    protected $fillable = ['proposal_template_item_id', 'filename', 'hashname', 'size', 'external_link'];

    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(ProposalTemplateItemImage::FILE_PATH . '/' . $this->proposal_template_item_id . '/' . $this->hashname);
    }

    public function getFileAttribute()
    {
        return $this->external_link ?: (ProposalTemplateItemImage::FILE_PATH . '/' . $this->proposal_template_item_id . '/' . $this->hashname);
    }

}
