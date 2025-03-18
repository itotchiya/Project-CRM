<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\EstimateItem
 *
 * @property int $id
 * @property int $estimate_id
 * @property string $item_name
 * @property string|null $item_summary
 * @property string $type
 * @property float $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $hsn_sac_code
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereUpdatedAt($value)
 * @property-read \App\Models\EstimateItemImage|null $estimateItemImage
 * @property-read mixed $tax_list
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereUnitId($value)
 * @mixin \Eloquent
 */
class EstimateItem extends BaseModel
{

    protected $guarded = ['id'];

    protected $with = ['estimateItemImage'];

    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    public function estimateItemImage(): HasOne
    {
        return $this->hasOne(EstimateItemImage::class, 'estimate_item_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function getTaxListAttribute()
    {
        $estimateItem = EstimateItem::findOrFail($this->id);
        $taxes = '';

        if ($estimateItem && $estimateItem->taxes) {
            $numItems = count(json_decode($estimateItem->taxes));

            if (!is_null($estimateItem->taxes)) {
                foreach (json_decode($estimateItem->taxes) as $index => $tax) {
                    $tax = $this->taxbyid($tax)->first();
                    $taxes .= $tax->tax_name . ': ' . $tax->rate_percent . '%';

                    $taxes = ($index + 1 != $numItems) ? $taxes . ', ' : $taxes;
                }
            }
        }

        return $taxes;
    }

}
