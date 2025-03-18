<?php

namespace App\Models;

use App\Traits\IconTrait;

/**
 * App\Models\InvoiceItemImage
 *
 * @property int $id
 * @property int $invoice_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $image
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereInvoiceItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereUpdatedAt($value)
 * @property int $invoice_recurring_item_id
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereInvoiceRecurringItemId($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
class RecurringInvoiceItemImage extends BaseModel
{

    use IconTrait;

    const FILE_PATH = 'recurring-invoice-files';

    protected $table = 'invoice_recurring_item_images';
    protected $appends = ['file_url', 'icon', 'file'];
    protected $fillable = ['invoice_recurring_item_id', 'filename', 'hashname', 'size', 'external_link'];

    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(RecurringInvoiceItemImage::FILE_PATH . '/' . $this->invoice_recurring_item_id . '/' . $this->hashname);
    }

    public function getFileAttribute()
    {
        return $this->external_link ?: (RecurringInvoiceItemImage::FILE_PATH . '/' . $this->invoice_recurring_item_id . '/' . $this->hashname);
    }

}
