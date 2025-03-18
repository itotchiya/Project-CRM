<?php

namespace App\Observers;

use App\Models\BankAccount;
use App\Models\BankTransaction;

class BankTransactionObserver
{

    public function saving(BankTransaction $bankTransaction)
    {
        if (user()) {
            $bankTransaction->last_updated_by = user()->id;
        }
    }

    public function creating(BankTransaction $bankTransaction)
    {
        if (user()) {
            $bankTransaction->added_by = user()->id;
        }

        if (company()) {
            $bankTransaction->company_id = company()->id;
        }

    }

    public function created(BankTransaction $bankTransaction)
    {
        $bankAccount = BankAccount::find($bankTransaction->bank_account_id);

        if (!is_null($bankAccount) && !is_null($bankTransaction)) {
            $bankBalance = $bankAccount->bank_balance;

            if (is_null($bankTransaction->type) || $bankTransaction->type == 'Cr') {
                $bankBalance += $bankTransaction->amount;
            }

            if ($bankTransaction->type == 'Dr') {
                $bankBalance -= $bankTransaction->amount;
            }

            $bankAccount->bank_balance = $bankBalance;

            $bankAccount->save();
        }
    }

}
