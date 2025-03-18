<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\EstimateTemplateItem
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $estimate_template_id
 * @property string|null $hsn_sac_code
 * @property string $item_name
 * @property string $type
 * @property int $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $item_summary
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EstimateTemplateItemImage|null $estimateTemplateItemImage
 * @property-read mixed $tax_list
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereEstimateTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereUpdatedAt($value)
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereUnitId($value)
 * @mixin \Eloquent
 */
class EstimateTemplateItem extends BaseModel
{

    // protected $table = 'estimate_template_items';

    protected $guarded = ['id'];

    protected $with = ['EstimateTemplateItemImage'];

    public function estimateTemplateItemImage(): HasOne
    {
        return $this->hasOne(EstimateTemplateItemImage::class, 'estimate_template_item_id');
    }

    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    public function getTaxListAttribute()
    {
        $estimateItemTax = $this->taxes;
        $taxes = '';

        if ($estimateItemTax) {
            $numItems = count(json_decode($estimateItemTax));

            if (!is_null($estimateItemTax)) {
                foreach (json_decode($estimateItemTax) as $index => $tax) {
                    $tax = $this->taxbyid($tax)->first();
                    $taxes .= $tax->tax_name . ': ' . $tax->rate_percent . '%';

                    $taxes = ($index + 1 != $numItems) ? $taxes . ', ' : $taxes;
                }
            }
        }

        return $taxes;
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

}
