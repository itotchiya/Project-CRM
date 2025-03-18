<?php

namespace App\Observers;

use App\Models\BankAccount;
use App\Models\BankTransaction;

class BankAccountObserver
{

    public function saving(BankAccount $bankAccount)
    {
        if (user()) {
            $bankAccount->last_updated_by = user()->id;
        }
    }

    public function creating(BankAccount $bankAccount)
    {
        if (user()) {
            $bankAccount->added_by = user()->id;
        }

        if (company()) {
            $bankAccount->company_id = company()->id;
        }
    }

    public function created(BankAccount $bankAccount)
    {
        $transaction = new BankTransaction();
        $transaction->company_id = $bankAccount->company_id;
        $transaction->bank_account_id = $bankAccount->id;
        $transaction->transaction_date = now()->format('Y-m-d');
        $transaction->amount = round($bankAccount->opening_balance, 2);
        $transaction->bank_balance = round($bankAccount->opening_balance, 2);
        $transaction->transaction_relation = 'bank';
        $transaction->title = 'bank-account-created';
        $transaction->save();
    }

    public function updating(BankAccount $bankAccount)
    {
        if ($bankAccount->isDirty('opening_balance')) {
            $originalBalance = $bankAccount->getOriginal('opening_balance');
            $getCurrentBalance = $bankAccount->opening_balance;
            $newBalance = 0;
            $currentBankBalance = 0;
            $currentBankAccount = BankAccount::find($bankAccount->id);
            $bankBalance = $currentBankAccount->bank_balance;

            $transaction = new BankTransaction();

            if ($bankAccount->getOriginal('opening_balance') > $bankAccount->opening_balance) {
                $newBalance = $originalBalance - $getCurrentBalance;
                $transaction->type = 'Dr';
                $currentBankBalance = $bankBalance - $newBalance;
            }

            if ($bankAccount->getOriginal('opening_balance') < $bankAccount->opening_balance) {
                $newBalance = $getCurrentBalance - $originalBalance;
                $currentBankBalance = $bankBalance + $newBalance;
            }

            $transaction->bank_account_id = $bankAccount->id;
            $transaction->amount = round($newBalance, 2);
            $transaction->transaction_date = now()->format('Y-m-d');
            $transaction->bank_balance = round($currentBankBalance, 2);
            $transaction->transaction_relation = 'bank';
            $transaction->title = 'bank-account-updated';
            $transaction->save();

        }

    }

}
