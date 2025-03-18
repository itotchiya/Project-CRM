<?php

namespace App\Models;

use App\Traits\HasMaskImage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCompany;

class InvoicePaymentDetail extends BaseModel
{
    use HasCompany, HasMaskImage;

    protected $table = 'invoice_payment_details';
    protected $fillable = ['title', 'company_id', 'payment_details'];

    protected $appends = ['image_url'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getImageUrlAttribute()
    {

        return ($this->image) ? asset_url_local_s3('offline-method/' . $this->image) : '-';
    }

    public function maskedImageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return ($this->image) ? $this->generateMaskedImageAppUrl('offline-method/' . $this->image) : '-';
            },
        );
    }
}
