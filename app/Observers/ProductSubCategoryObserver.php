<?php

namespace App\Observers;

use App\Models\ProductSubCategory;

class ProductSubCategoryObserver
{

    public function creating(ProductSubCategory $productSubCategory)
    {
        if (company()) {
            $productSubCategory->company_id = company()->id;
        }
    }

}
