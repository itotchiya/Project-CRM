<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ContractTemplate
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $subject
 * @property string|null $description
 * @property string $amount
 * @property int $contract_type_id
 * @property int|null $currency_id
 * @property string|null $contract_detail
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ContractType $contractType
 * @property-read \App\Models\Currency|null $currency
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereContractDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereContractTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereUpdatedAt($value)
 * @property int|null $contract_template_number
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereContractTemplateNumber($value)
 * @mixin \Eloquent
 */
class ContractTemplate extends BaseModel
{

    use HasFactory, HasCompany;

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

}
