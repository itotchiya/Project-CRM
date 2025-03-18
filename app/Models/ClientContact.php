<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ClientContact
 *
 * @property int $id
 * @property int $user_id
 * @property string $contact_name
 * @property string|null $phone
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $client
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereUserId($value)
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static Builder|ClientContact whereAddedBy($value)
 * @method static Builder|ClientContact whereLastUpdatedBy($value)
 * @property string|null $title
 * @method static Builder|ClientContact whereTitle($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static Builder|ClientContact whereCompanyId($value)
 * @mixin \Eloquent
 */
class ClientContact extends BaseModel
{

    protected $with = [
                'client'
        ];

    use HasCompany;

    protected $fillable = ['user_id', 'client_id' ,'contact_name', 'email', 'phone', 'title', 'address'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
