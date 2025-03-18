<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ProposalItemImage
 *
 * @property int $id
 * @property int $proposal_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereProposalItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereUpdatedAt($value)
 * @property-read \App\Models\ProposalItem $item
 * @property-read mixed $file
 * @mixin \Eloquent
 */
class ProposalItemImage extends BaseModel
{

    use IconTrait;

    const FILE_PATH = 'proposal-files';

    protected $appends = ['file_url', 'icon', 'file'];
    protected $fillable = ['proposal_item_id', 'filename', 'hashname', 'size', 'external_link'];

    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(ProposalItemImage::FILE_PATH . '/' . $this->proposal_item_id . '/' . $this->hashname);
    }

    public function getFileAttribute()
    {
        return $this->external_link ?: (ProposalItemImage::FILE_PATH . '/' . $this->proposal_item_id . '/' . $this->hashname);
    }

    public function item() : BelongsTo
    {
        return $this->belongsTo(ProposalItem::class, 'proposal_item_id');
    }

}
