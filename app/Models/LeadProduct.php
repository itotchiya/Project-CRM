<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\LeadProduct
 *
 * @property int $id
 * @property int $deal_id
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lead $lead
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereDealId($value)
 * @mixin \Eloquent
 */
class LeadProduct extends Pivot
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'lead_products';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

}
