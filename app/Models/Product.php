<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Traits\CustomFieldsTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property string $price
 * @property string|null $taxes
 * @property int $allow_purchase
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $description
 * @property int|null $unit_id
 * @property int|null $category_id
 * @property int|null $sub_category_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property string|null $hsn_sac_code
 * @property string|null $sku
 * @property-read mixed $icon
 * @property-read mixed $total_amount
 * @property-read \App\Models\Tax $tax
 * @method static \Database\Factories\ProductFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereAllowPurchase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSubCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @property-read \App\Models\ProductCategory|null $category
 * @property string|null $image
 * @property-read mixed $image_url
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereImage($value)
 * @property int $downloadable
 * @property string|null $downloadable_file
 * @property string|null $default_image
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductFiles[] $files
 * @property-read int|null $files_count
 * @property-read mixed $download_file_url
 * @property-read mixed $extras
 * @property-read \App\Models\ProductSubCategory|null $subCategory
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDefaultImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDownloadable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDownloadableFile($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $tax_list
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read int|null $leads_count
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUnitId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItem
 * @property-read int|null $order_item_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItem
 * @property string|null $purchase_price
 * @property string $purchase_information
 * @property string $track_inventory
 * @property string|null $sales_description
 * @property string|null $purchase_description
 * @property int|null $opening_stock
 * @property float|null $rate_per_unit
 * @property string|null $sku
 * @property string|null $type
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItem
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereOpeningStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePurchaseDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePurchaseInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereRatePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSalesDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereTrackInventory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereType($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseStockAdjustment> $inventory
 * @property-read int|null $inventory_count
 * @mixin \Eloquent
 */
class Product extends BaseModel
{

    use HasCompany;
    use HasFactory, CustomFieldsTrait;

    protected $table = 'products';
    const FILE_PATH = 'products';

    protected $fillable = ['name', 'price', 'description', 'taxes'];

    protected $appends = ['total_amount', 'image_url', 'download_file_url', 'image'];

    protected $with = ['tax'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Product';

    public function getImageUrlAttribute()
    {
        if (app()->environment(['development','demo']) && str_contains($this->default_image, 'http')) {
            return $this->default_image;
        }

        return ($this->default_image) ? asset_url_local_s3(Product::FILE_PATH . '/' . $this->default_image) : '';
    }

    public function getImageAttribute()
    {
        if($this->default_image){
            return str($this->default_image)->contains('http') ? $this->default_image : (Product::FILE_PATH . '/' . $this->default_image);
        }

        return $this->default_image;
    }

    public function getDownloadFileUrlAttribute()
    {
        return ($this->downloadable_file) ? asset_url_local_s3(Product::FILE_PATH . '/' . $this->downloadable_file) : null;
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class)->withTrashed();
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class, 'lead_products');
    }

    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ProductSubCategory::class, 'sub_category_id');
    }

    public function getTotalAmountAttribute()
    {

        if (!is_null($this->price) && !is_null($this->tax)) {
            return (int)$this->price + ((int)$this->price * ((int)$this->tax->rate_percent / 100));
        }

        return '';
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductFiles::class, 'product_id')->orderByDesc('id');
    }

    public function getTaxListAttribute()
    {
        $productItem = Product::findOrFail($this->id);
        $taxes = '';

        if ($productItem && $productItem->taxes) {
            $numItems = count(json_decode($productItem->taxes));

            if (!is_null($productItem->taxes)) {
                foreach (json_decode($productItem->taxes) as $index => $tax) {
                    $tax = $this->taxbyid($tax)->first();
                    $taxes .= $tax->tax_name . ': ' . $tax->rate_percent . '%';

                    $taxes = ($index + 1 != $numItems) ? $taxes . ', ' : $taxes;
                }
            }
        }

        return $taxes;
    }

    public function orderItem(): HasMany
    {
        return $this->hasMany(OrderItems::class, 'product_id');

    }

    public function inventory()
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(PurchaseStockAdjustment::class, 'product_id');
    }

}
