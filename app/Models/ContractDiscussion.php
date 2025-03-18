<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ContractDiscussion
 *
 * @property int $id
 * @property int $contract_id
 * @property int $from
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereUpdatedAt($value)
 * @property-read \App\Models\Contract $contract
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereCompanyId($value)
 * @mixin \Eloquent
 */
class ContractDiscussion extends BaseModel
{

    use HasCompany;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from', 'id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

}
