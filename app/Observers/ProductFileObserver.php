<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\ProductFiles;

class ProductFileObserver
{

    public function saving(ProductFiles $productFiles)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $productFiles->last_updated_by = user()->id;
        }

    }

    public function creating(ProductFiles $productFiles)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $productFiles->added_by = user()->id;
        }

        if (company()) {
            $productFiles->company_id = company()->id;
        }
    }

    public function deleting(ProductFiles $productFiles)
    {
        $productFiles->load('product');

        if (!isRunningInConsoleOrSeeding()) {
            if (isset($productFiles->product) && $productFiles->product->default_image == $productFiles->hashname) {
                $productFiles->product->default_image = null;
                $productFiles->product->save();
            }
        }

        Files::deleteFile($productFiles->hashname, ProductFiles::FILE_PATH);
    }

}
