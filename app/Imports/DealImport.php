<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class DealImport implements ToArray
{

    public static function fields(): array
    {
        return array(
            array('id' => 'email', 'name' => __('modules.deal.leadContactEmail'), 'required' => 'Yes'),
            array('id' => 'name', 'name' => __('modules.deal.dealName'), 'required' => 'Yes'),
            array('id' => 'pipeline', 'name' => __('modules.deal.pipeline'), 'required' => 'Yes'),
            array('id' => 'stages', 'name' => __('modules.deal.stages'), 'required' => 'Yes'),
            array('id' => 'value', 'name' => __('modules.deal.dealValue'), 'required' => 'Yes'),
            array('id' => 'close_date', 'name' => __('modules.deal.closeDate'), 'required' => 'Yes'),
        );
    }

    public function array(array $array): array
    {
        return $array;
    }

}
