<?php

namespace App\Observers;

use App\Events\NewExpenseEvent;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Expense;
use App\Models\Notification;
use App\Traits\EmployeeActivityTrait;

class ExpenseObserver
{
    use EmployeeActivityTrait;

    public function saving(Expense $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->last_updated_by = user()->id;
        }
    }

    public function creating(Expense $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->added_by = user()->id;
        }

        if (company()) {
            $expense->company_id = company()->id;
        }

    }

    public function created(Expense $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'expenses-created', $expense->id, 'expenses');
            }

            if ($expense->user_id != '') {
                event(new NewExpenseEvent($expense, 'member'));
            }

            if ($expense->user_id != '' && $expense->user_id != user()->id) {
                event(new NewExpenseEvent($expense, 'admin'));
            }
        }


        if (!isRunningInConsoleOrSeeding()) {

            if (!is_null($expense->bank_account_id) && $expense->status == 'approved') {

                $bankAccount = BankAccount::find($expense->bank_account_id);
                $bankBalance = $bankAccount->bank_balance;
                $totalBalance = $bankBalance - $expense->price;

                $transaction = new BankTransaction();
                $transaction->bank_account_id = $expense->bank_account_id;
                $transaction->expense_id = $expense->id;
                $transaction->transaction_date = $expense->purchase_date;
                $transaction->amount = round($expense->price, 2);
                $transaction->type = 'Dr';
                $transaction->bank_balance = round($totalBalance, 2);
                $transaction->transaction_relation = 'expense';
                $transaction->transaction_related_to = $expense->item_name;
                $transaction->title = 'expense-added';
                $transaction->save();
            }
        }

    }

    public function updating(Expense $expense)
    {

        if (!isRunningInConsoleOrSeeding()) {

            if ($expense->isDirty('status') && $expense->status == 'approved') {
                $expense->approver_id = user()->id;
            }
        }

        if (!isRunningInConsoleOrSeeding()) {

            if (!is_null($expense->bank_account_id) && $expense->status == 'approved') {

                if ($expense->isDirty('bank_account_id')) {
                    $account = $expense->getOriginal('bank_account_id');
                    $oldPrice = $expense->getOriginal('price');
                    $newPrice = $expense->price;

                    $bankAccount = BankAccount::find($account);

                    if ($bankAccount && $expense->getOriginal('status') == 'approved') {

                        $bankBalance = $bankAccount->bank_balance;
                        $bankBalance += $oldPrice;

                        $transaction = new BankTransaction();
                        $transaction->expense_id = $expense->id;
                        $transaction->type = 'Cr';
                        $transaction->bank_account_id = $account;
                        $transaction->amount = round($oldPrice, 2);
                        $transaction->transaction_date = $expense->purchase_date;
                        $transaction->bank_balance = round($bankBalance, 2);
                        $transaction->transaction_relation = 'expense';
                        $transaction->transaction_related_to = $expense->item_name;
                        $transaction->title = 'expense-modified';
                        $transaction->save();

                        $bankAccount->bank_balance = round($bankBalance, 2);
                        $bankAccount->save();
                    }

                    $newBankAccount = BankAccount::find($expense->bank_account_id);

                    if ($newBankAccount) {
                        $newBankBalance = $newBankAccount->bank_balance;
                        $newBankBalance -= $newPrice;

                        $transaction = new BankTransaction();
                        $transaction->expense_id = $expense->id;
                        $transaction->type = 'Dr';
                        $transaction->bank_account_id = $expense->bank_account_id;
                        $transaction->amount = round($newPrice, 2);
                        $transaction->transaction_date = $expense->purchase_date;
                        $transaction->bank_balance = round($newBankBalance, 2);
                        $transaction->transaction_relation = 'expense';
                        $transaction->transaction_related_to = $expense->item_name;
                        $transaction->title = 'expense-added';
                        $transaction->save();

                        $newBankAccount->bank_balance = round($newBankBalance, 2);
                        $newBankAccount->save();
                    }

                }
                elseif (!$expense->isDirty('bank_account_id') && $expense->isDirty('price')) {
                    $bankAccount = BankAccount::find($expense->bank_account_id);
                    $bankBalance = $bankAccount->bank_balance;

                    $account = $expense->getOriginal('bank_account_id');
                    $oldPrice = $expense->getOriginal('price');
                    $newPrice = $expense->price;

                    if ($expense->getOriginal('price') > $expense->price) {
                        $newBalance = $oldPrice - $newPrice;
                        $bankBalance += $newBalance;

                        $transaction = new BankTransaction();
                        $transaction->expense_id = $expense->id;
                        $transaction->type = 'Cr';
                        $transaction->bank_account_id = $account;
                        $transaction->amount = round($newBalance, 2);
                        $transaction->transaction_date = $expense->purchase_date;
                        $transaction->bank_balance = round($bankBalance, 2);
                        $transaction->transaction_relation = 'expense';
                        $transaction->transaction_related_to = $expense->item_name;
                        $transaction->title = 'expense-modified';
                        $transaction->save();
                    }

                    if ($expense->getOriginal('price') < $expense->price) {
                        $newBalance = $newPrice - $oldPrice;
                        $bankBalance -= $newBalance;

                        $transaction = new BankTransaction();
                        $transaction->expense_id = $expense->id;
                        $transaction->type = 'Dr';
                        $transaction->bank_account_id = $account;
                        $transaction->amount = round($newBalance, 2);
                        $transaction->transaction_date = $expense->purchase_date;
                        $transaction->bank_balance = round($bankBalance, 2);
                        $transaction->transaction_relation = 'expense';
                        $transaction->transaction_related_to = $expense->item_name;
                        $transaction->title = 'expense-modified';
                        $transaction->save();
                    }

                    $bankAccount->bank_balance = round($bankBalance, 2);
                    $bankAccount->save();

                }
                elseif ($expense->isDirty('status')) {
                    $bankAccount = BankAccount::find($expense->bank_account_id);
                    $bankBalance = $bankAccount->bank_balance;

                    $newBalance = $bankBalance - $expense->price;

                    $transaction = new BankTransaction();
                    $transaction->expense_id = $expense->id;
                    $transaction->type = 'Dr';
                    $transaction->bank_account_id = $expense->bank_account_id;
                    $transaction->amount = round($expense->price, 2);
                    $transaction->transaction_date = $expense->purchase_date;
                    $transaction->bank_balance = round($newBalance, 2);
                    $transaction->transaction_relation = 'expense';
                    $transaction->transaction_related_to = $expense->item_name;
                    $transaction->title = 'expense-added';
                    $transaction->save();

                    $bankAccount->bank_balance = round($newBalance, 2);
                    $bankAccount->save();
                }

            }

            if ($expense->isDirty('status') && $expense->getOriginal('status') == 'approved' && $expense->status != 'approved') {
                $bankAccount = BankAccount::find($expense->bank_account_id);

                if (!is_null($bankAccount)) {
                    $bankBalance = $bankAccount->bank_balance;

                    $newBalance = $bankBalance + $expense->price;

                    $transaction = new BankTransaction();
                    $transaction->expense_id = $expense->id;
                    $transaction->type = 'Cr';
                    $transaction->bank_account_id = $expense->bank_account_id;
                    $transaction->amount = round($expense->price, 2);
                    $transaction->transaction_date = $expense->purchase_date;
                    $transaction->bank_balance = round($newBalance, 2);
                    $transaction->transaction_relation = 'expense';
                    $transaction->transaction_related_to = $expense->item_name;
                    $transaction->title = 'expense-added';
                    $transaction->save();

                    $bankAccount->bank_balance = round($newBalance, 2);
                    $bankAccount->save();
                }

            }

        }

    }

    public function updated(Expense $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'expenses-updated', $expense->id, 'expenses');
            }

            if ($expense->isDirty('status') && $expense->user_id != '' && $expense->user_id != user()->id) {
                event(new NewExpenseEvent($expense, 'status'));
            }

        }
    }

    public function deleting(Expense $expense)
    {
        $notifyData = ['App\Notifications\NewExpenseAdmin', 'App\Notifications\NewExpenseMember', 'App\Notifications\NewExpenseStatus'];

        Notification::
        whereIn('type', $notifyData)
            ->whereNull('read_at')
            ->where('data', 'like', '{"id":' . $expense->id . ',%')
            ->delete();

        if (!is_null($expense->bank_account_id) && $expense->status == 'approved') {

            $account = $expense->bank_account_id;
            $price = $expense->price;

            $bankAccount = BankAccount::find($account);

            if ($bankAccount) {
                $bankBalance = $bankAccount->bank_balance;
                $bankBalance += $price;

                $transaction = new BankTransaction();
                $transaction->expense_id = $expense->id;
                $transaction->type = 'Cr';
                $transaction->bank_account_id = $account;
                $transaction->amount = round($price, 2);
                $transaction->transaction_date = $expense->purchase_date;
                $transaction->bank_balance = round($bankBalance, 2);
                $transaction->transaction_relation = 'expense';
                $transaction->transaction_related_to = $expense->item_name;
                $transaction->title = 'expense-deleted';
                $transaction->save();

                $bankAccount->bank_balance = round($bankBalance, 2);
                $bankAccount->save();
            }
        }

    }

    public function deleted(Expense $expense)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'expenses-deleted');

        }
    }

}
