<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $currencyId = Currency::where('company_id', $companyId)->first()->id;;

        DB::beginTransaction();

        $bankAccounts = ['Primary Account', 'Secondary Account'];

        foreach ($bankAccounts as $key => $bankAccount) {
            $account = new BankAccount();
            $account->company_id    = $companyId;
            $account->type    = 'bank';
            $account->account_name    = fake()->company();
            $account->account_type  = 'current';
            $account->currency_id     = $currencyId;
            $account->contact_number  = fake()->phoneNumber();
            $account->opening_balance = fake()->numberBetween(10000, 99999);
            $account->status          = 1;
            $account->bank_name    = $bankAccount;
            /** @phpstan-ignore-next-line */
            $account->account_number  = fake()->bankAccountNumber();
            $account->save();
        }

        DB::commit();

    }

}
