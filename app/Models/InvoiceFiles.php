<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\InvoiceFiles
 *
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\Invoice|null $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles query()
 * @property int $id
 * @property int $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property string|null $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InvoiceFiles extends BaseModel
{

    use IconTrait;

    const FILE_PATH = 'invoices';

    protected $fillable = [];

    protected $guarded = ['id'];

    protected $table = 'invoice_files';
    public $dates = ['created_at', 'updated_at'];

    protected $appends = ['file_url', 'icon'];

    public $timestamps = false;

    public function getFileUrlAttribute()
    {
        return asset_url_local_s3(InvoiceFiles::FILE_PATH . '/' . $this->hashname);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

}
