<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\EstimateTemplate
 *
 * @property int|null $unit_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCompanyId($value)
 * @property int $id
 * @property int|null $company_id
 * @property string $name
 * @property float $sub_total
 * @property float $total
 * @property int|null $currency_id
 * @property string $discount_type
 * @property float $discount
 * @property int $invoice_convert
 * @property string $status
 * @property string|null $note
 * @property string|null $description
 * @property string $calculate_tax
 * @property string|null $client_comment
 * @property int $signature_approval
 * @property string|null $hash
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClientDetails|null $clients
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplateItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\UnitType|null $units
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereCalculateTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereClientComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereInvoiceConvert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereSignatureApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */

class EstimateTemplate extends BaseModel
{
    use HasCompany;

    protected $table = 'estimate_templates';

    public function items(): HasMany
    {
        return $this->hasMany(EstimateTemplateItem::class, 'estimate_template_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function clients(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class);
    }

    public function units(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

}
