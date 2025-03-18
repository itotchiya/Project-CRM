<?php

namespace App\Observers;

use App\Models\Product;
use App\Traits\UnitTypeSaveTrait;
use App\Traits\EmployeeActivityTrait;

class ProductObserver
{

    use UnitTypeSaveTrait;
    use EmployeeActivityTrait;

    public function saving(Product $product)
    {
        $this->unitType($product);

        if (!isRunningInConsoleOrSeeding()) {
            $product->last_updated_by = user() ? user()->id : null;
        }
    }

    public function created(Product $product)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'product-created', $product->id, 'product');



        }
    }

    public function creating(Product $product)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $product->added_by = user() ? user()->id : null;
        }

        if (company()) {
            $product->company_id = company()->id;
        }
    }

    public function updated(Product $product)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'product-updated', $product->id, 'product');



        }
    }

    public function deleted(Product $product)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'product-deleted');

        }
    }

    public function deleting(Product $product)
    {
        $product->files()->each(function ($file) {
            $file->delete();
        });
    }

}
