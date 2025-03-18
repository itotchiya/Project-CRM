<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ProposalTemplate
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $name
 * @property int|null $unit_id
 * @property float $sub_total
 * @property float $total
 * @property int|null $currency_id
 * @property string $discount_type
 * @property float $discount
 * @property int $invoice_convert
 * @property string|null $description
 * @property string|null $client_comment
 * @property int $signature_approval
 * @property string|null $hash
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProposalTemplateItem[] $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Lead $lead
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereClientComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereInvoiceConvert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereSignatureApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereUpdatedAt($value)
 * @property-read UnitType|null $units
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereUnitId($value)
 * @property-read \App\Models\Company|null $company
 * @mixin \Eloquent
 */
class ProposalTemplate extends BaseModel
{

    use HasCompany;

    protected $table = 'proposal_templates';

    public function items(): HasMany
    {
        return $this->hasMany(ProposalTemplateItem::class, 'proposal_template_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function units(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

}
