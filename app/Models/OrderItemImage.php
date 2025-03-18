<?php

namespace App\Models;

use App\Traits\IconTrait;

/**
 * App\Models\OrderItemImage
 *
 * @property int $id
 * @property int|null $order_item_id
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereUpdatedAt($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
class OrderItemImage extends BaseModel
{

    use IconTrait;

    protected $appends = ['file_url', 'icon', 'file'];

    protected $fillable = ['order_item_id', 'external_link'];

    public function getFileUrlAttribute()
    {
        return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
    }

    public function getFileAttribute()
    {
        return $this->external_link;
    }

}
