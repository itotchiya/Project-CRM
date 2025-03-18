<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ExpenseImport implements ToArray
{

    public static function fields(): array
    {
        return array(
            array('id' => 'item_name', 'name' => __('modules.expenses.itemName'), 'required' => 'Yes',),
            array('id' => 'price', 'name' => __('app.price'), 'required' => 'Yes',),
            array('id' => 'purchase_date', 'name' => __('modules.expenses.purchaseDate'), 'required' => 'Yes',),
            array('id' => 'email', 'name' => __('modules.employees.employeeEmail'), 'required' => 'No',),
            array('id' => 'purchase_from', 'name' => __('modules.expenses.purchaseFrom'), 'required' => 'No',),
            array('id' => 'description', 'name' => __('app.description'), 'required' => 'No'),
            array('id' => 'bank_account', 'name' => __('app.menu.bankaccount'), 'required' => 'No'),
            array('id' => 'category', 'name' => __('modules.expenses.expenseCategory'), 'required' => 'No',),
        );
    }

    public function array(array $array): array
    {
        return $array;
    }

}
