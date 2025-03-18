<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CreditNoteItem
 *
 * @property int $id
 * @property int $credit_note_id
 * @property string $item_name
 * @property string $type
 * @property int $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $hsn_sac_code
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereCreditNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereUpdatedAt($value)
 * @property-read \App\Models\CreditNoteItemImage|null $creditNoteItemImage
 * @property string|null $item_summary
 * @property-read mixed $tax_list
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereItemSummary($value)
 * @property-read \App\Models\UnitType|null $unit
 * @property int|null $unit_id
 * @property int|null $product_id
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereUnitId($value)
 * @mixin \Eloquent
 */
class CreditNoteItem extends BaseModel
{

    protected $guarded = ['id'];

    protected $with = ['creditNoteItemImage'];

    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    public function creditNoteItemImage(): HasOne
    {
        return $this->hasOne(CreditNoteItemImage::class, 'credit_note_item_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function getTaxListAttribute()
    {
        $creditNoteItem = CreditNoteItem::findOrFail($this->id);
        $taxes = '';

        if ($creditNoteItem && $creditNoteItem->taxes) {
            $numItems = count(json_decode($creditNoteItem->taxes));

            if (!is_null($creditNoteItem->taxes)) {
                foreach (json_decode($creditNoteItem->taxes) as $index => $tax) {
                    $tax = $this->taxbyid($tax)->first();
                    $taxes .= $tax->tax_name . ': ' . $tax->rate_percent . '%';

                    $taxes = ($index + 1 != $numItems) ? $taxes . ', ' : $taxes;
                }
            }
        }

        return $taxes;
    }

}
