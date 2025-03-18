<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\VisaDetail
 *
 * @property string|null $image
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCompanyId($value)
 * @property int $id
 * @property int|null $company_id
 * @property int|null $user_id
 * @property int|null $country_id
 * @property int|null $added_by
 * @property string $visa_number
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $expiry_date
 * @property string|null $file
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Country|null $country
 * @property-read mixed $image_url
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereVisaNumber($value)
 * @mixin \Eloquent
 */

class VisaDetail extends BaseModel
{
    use HasCompany;

    const FILE_PATH = 'visa';
    // protected $table = 'passport';
    protected $appends = ['image_url'];
    protected $casts = [
        'issue_date' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute()
    {
        return asset_url_local_s3(VisaDetail::FILE_PATH . '/'  . $this->file);
    }

    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

}
