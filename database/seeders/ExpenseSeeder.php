<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\EmployeeDetails;
use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $employees = EmployeeDetails::where('company_id', $companyId)->get()->pluck('user_id')->toArray();
        $employeeId = $employees[array_rand($employees)];
        $currencies = Currency::where('company_id', $companyId)->get()->pluck('id')->toArray();
        $currencyId = $currencies[array_rand($currencies)];
        $count = config('app.seed_record_count');

        Expense::factory()
            ->count((int)$count)
            ->make()
            ->each(function (Expense $expense) use ($companyId, $employeeId, $currencyId) {
                $expense->company_id = $companyId;
                $expense->currency_id = $currencyId;
                $expense->user_id = $employeeId;
                $expense->exchange_rate = 1;
                $expense->save();
            });
    }

}
