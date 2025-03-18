<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Passport
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $user_id
 * @property int|null $country_id
 * @property int|null $added_by
 * @property string $passport_number
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $expiry_date
 * @property string|null $file
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Country|null $country
 * @property-read mixed $image_url
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Passport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Passport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Passport query()
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport wherePassportNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereUserId($value)
 * @mixin \Eloquent
 */
class Passport extends BaseModel
{

    use HasCompany;

    protected $table = 'passport_details';
    const FILE_PATH = 'passport';

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
        return asset_url_local_s3(Passport::FILE_PATH . '/' . $this->file);
    }

    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

}
