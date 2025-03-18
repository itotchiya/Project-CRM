<?php

namespace App\Observers;

use App\Models\ProductCategory;

class ProductCategoryObserver
{

    public function creating(ProductCategory $productCategory)
    {
        if (company()) {
            $productCategory->company_id = company()->id;
        }
    }

}
