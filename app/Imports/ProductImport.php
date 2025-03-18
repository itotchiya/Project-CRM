<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ProductImport implements ToArray
{

    public static function fields(): array
    {
        return array(
            array('id' => 'product_name', 'name' => __('modules.client.productName'), 'required' => 'Yes'),
            array('id' => 'price', 'name' => __('app.price'), 'required' => 'Yes'),
            array('id' => 'unit_type', 'name' => __('modules.unitType.unitType'), 'required' => 'No'),
            array('id' => 'product_category', 'name' => __('modules.productCategory.productCategory'), 'required' => 'No'),
            array('id' => 'product_sub_category', 'name' => __('modules.productCategory.productSubCategory'), 'required' => 'No'),
            array('id' => 'sku', 'name' => __('app.sku'), 'required' => 'No'),
            array('id' => 'description', 'name' => __('app.description'), 'required' => 'No'),
        );
    }

    public function array(array $array): array
    {
        return $array;
    }

}
