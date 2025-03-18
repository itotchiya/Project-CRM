<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Events\NewPaymentEvent;
use App\Scopes\ActiveScope;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Events\InvoicePaymentReceivedEvent;
use App\Http\Controllers\QuickbookController;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Traits\EmployeeActivityTrait;

class PaymentObserver
{
    use EmployeeActivityTrait;

    public function saving(Payment $payment)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $payment->last_updated_by = user()->id;
        }
    }

    public function creating(Payment $payment)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $payment->added_by = user() ? user()->id : null;
        }

        $payment->company_id = $payment->currency?->company_id;

    }

    public function saved(Payment $payment)
    {
        if (isRunningInConsoleOrSeeding()) {
            return;
        }

        if (($payment->project_id && $payment->project->client_id != null) || ($payment->invoice_id && $payment->invoice->client_id != null) && $payment->gateway != 'Offline') {
            // Notify client
            $clientId = ($payment->project_id && $payment->project->client_id != null) ? $payment->project->client_id : $payment->invoice->client_id;

            $admins = User::allAdmins($payment->company->id);

            $client_details = User::withoutGlobalScope(ActiveScope::class)->where('id', $clientId)->get();
            $notifyUser = $client_details;

            $notifyUsers = $notifyUser->merge($admins);

            if ($notifyUser && $payment->status === 'complete') {
                event(new NewPaymentEvent($payment, $notifyUsers));
            }
        }
    }

    public function created(Payment $payment)
    {
        if (($payment->invoice_id || $payment->order_id) && $payment->status == 'complete') {

            if ($payment->invoice_id) {
                $invoice = $payment->invoice;
            }
            elseif ($payment->order_id) {
                $invoice = Invoice::where('order_id', $payment->invoice_id)->latest()->first();
            }

            $due = 0;

            if (isset($payment->invoice)) {
                $due = $payment->invoice->due_amount;
            }
            elseif (isset($payment->order)) {
                $due = $payment->order->total;
            }

            $dueAmount = $due - $payment->amount;

            if($dueAmount > 0){
                $payment->invoice->status = 'partial';
            }else{
                $payment->invoice->status = 'paid';
            }

            if (isset($invoice)) {
                $invoice->due_amount = $dueAmount;
            }

            $invoice->save();


            try {
                if (!isRunningInConsoleOrSeeding()) {

                    if ($payment->gateway != 'Offline') {
                        event(new InvoicePaymentReceivedEvent($payment));
                    }

                    if (quickbooks_setting()->status && quickbooks_setting()->access_token != '') {
                        $quickBooks = new QuickbookController();
                        $qbPaymentId = $quickBooks->createPayment($payment);

                        $payment->quickbooks_payment_id = $qbPaymentId;
                        $payment->saveQuietly();
                    }
                }
            } catch (Exception $e) {
                Log::info($e);
            }

        }

        if (!is_null($payment->bank_account_id) && $payment->status == 'complete') {

            $bankAccount = BankAccount::find($payment->bank_account_id);
            $bankBalance = $bankAccount->bank_balance;
            $totalBalance = $bankBalance + $payment->amount;

            $transaction = new BankTransaction();
            $transaction->company_id = $payment->company_id;
            $transaction->bank_account_id = $payment->bank_account_id;
            $transaction->payment_id = $payment->id;
            $transaction->invoice_id = $payment->invoice_id;
            $transaction->amount = round($payment->amount, 2);
            $transaction->transaction_date = $payment->paid_on;
            $transaction->bank_balance = round($totalBalance);
            $transaction->transaction_relation = 'payment';
            $transaction->transaction_related_to = $payment->id;
            $transaction->title = 'payment-credited';
            $transaction->save();

        }

    }

    public function updating(Payment $payment)
    {
        if (isRunningInConsoleOrSeeding()) {
            return true;
        }

        if (!is_null($payment->bank_account_id) && $payment->status == 'complete') {

            if ($payment->isDirty('bank_account_id')) {
                $originalAccount = $payment->getOriginal('bank_account_id');
                $oldAmount = $payment->getOriginal('amount');
                $newAmount = $payment->amount;

                $bankAccount = BankAccount::find($originalAccount);

                if ($bankAccount && $payment->getOriginal('status') == 'complete') {
                    $bankBalance = $bankAccount->bank_balance;
                    $bankBalance -= $oldAmount;

                    $transaction = new BankTransaction();
                    $transaction->payment_id = $payment->id;
                    $transaction->invoice_id = $payment->invoice_id;
                    $transaction->type = 'Dr';
                    $transaction->bank_account_id = $originalAccount;
                    $transaction->amount = round($oldAmount, 2);
                    $transaction->transaction_date = $payment->paid_on;
                    $transaction->bank_balance = round($bankBalance, 2);
                    $transaction->transaction_relation = 'payment';
                    $transaction->transaction_related_to = $payment->id;
                    $transaction->title = 'payment-debited';
                    $transaction->save();

                    $bankAccount->bank_balance = round($bankBalance, 2);
                    $bankAccount->save();
                }

                $newBankAccount = BankAccount::find($payment->bank_account_id);

                if ($newBankAccount) {
                    $newBankBalance = $newBankAccount->bank_balance;
                    $newBankBalance += $newAmount;

                    $transaction = new BankTransaction();
                    $transaction->payment_id = $payment->id;
                    $transaction->invoice_id = $payment->invoice_id;
                    $transaction->type = 'Cr';
                    $transaction->bank_account_id = $payment->bank_account_id;
                    $transaction->amount = round($newAmount, 2);
                    $transaction->transaction_date = $payment->paid_on;
                    $transaction->bank_balance = round($newBankBalance, 2);
                    $transaction->transaction_relation = 'payment';
                    $transaction->transaction_related_to = $payment->id;
                    $transaction->title = 'payment-credited';
                    $transaction->save();

                    $newBankAccount->bank_balance = round($newBankBalance, 2);
                    $newBankAccount->save();
                }

            }
            elseif (!$payment->isDirty('bank_account_id') && $payment->isDirty('amount')) {
                $bankAccount = BankAccount::find($payment->bank_account_id);
                $bankBalance = $bankAccount->bank_balance;

                $account = $payment->getOriginal('bank_account_id');
                $oldAmount = $payment->getOriginal('amount');
                $newAmount = $payment->amount;

                if ($payment->getOriginal('amount') > $payment->amount) {
                    $newBalance = $oldAmount - $newAmount;
                    $bankBalance -= $newBalance;

                    $transaction = new BankTransaction();
                    $transaction->payment_id = $payment->id;
                    $transaction->invoice_id = $payment->invoice_id;
                    $transaction->type = 'Dr';
                    $transaction->bank_account_id = $account;
                    $transaction->amount = round($newBalance, 2);
                    $transaction->transaction_date = $payment->paid_on;
                    $transaction->bank_balance = round($bankBalance, 2);
                    $transaction->transaction_relation = 'payment';
                    $transaction->transaction_related_to = $payment->id;
                    $transaction->title = 'payment-updated';
                    $transaction->save();
                }

                if ($payment->getOriginal('amount') < $payment->amount) {
                    $newBalance = $newAmount - $oldAmount;
                    $bankBalance += $newBalance;

                    $transaction = new BankTransaction();
                    $transaction->payment_id = $payment->id;
                    $transaction->invoice_id = $payment->invoice_id;
                    $transaction->type = 'Cr';
                    $transaction->bank_account_id = $account;
                    $transaction->amount = round($newBalance, 2);
                    $transaction->transaction_date = $payment->paid_on;
                    $transaction->bank_balance = round($bankBalance, 2);
                    $transaction->transaction_relation = 'payment';
                    $transaction->transaction_related_to = $payment->id;
                    $transaction->title = 'payment-updated';
                    $transaction->save();
                }

                $bankAccount->bank_balance = round($bankBalance, 2);
                $bankAccount->save();

            }
            elseif ($payment->isDirty('status')) {
                $bankAccount = BankAccount::find($payment->bank_account_id);
                $bankBalance = $bankAccount->bank_balance;

                $newBalance = $bankBalance + $payment->amount;

                $transaction = new BankTransaction();
                $transaction->payment_id = $payment->id;
                $transaction->type = 'Cr';
                $transaction->bank_account_id = $payment->bank_account_id;
                $transaction->amount = round($payment->amount, 2);
                $transaction->transaction_date = $payment->paid_on;
                $transaction->bank_balance = round($newBalance, 2);
                $transaction->transaction_relation = 'payment';
                $transaction->transaction_related_to = $payment->id;
                $transaction->title = 'payment-credited';
                $transaction->save();

                $bankAccount->bank_balance = round($newBalance, 2);
                $bankAccount->save();
            }

        }

        if ($payment->isDirty('status') && $payment->status != 'complete') {
            $bankAccount = BankAccount::find($payment->bank_account_id);

            if (!is_null($bankAccount)) {
                $bankBalance = $bankAccount->bank_balance;

                $newBalance = $bankBalance - $payment->amount;

                $transaction = new BankTransaction();
                $transaction->payment_id = $payment->id;
                $transaction->type = 'Dr';
                $transaction->bank_account_id = $payment->bank_account_id;
                $transaction->amount = round($payment->amount, 2);
                $transaction->transaction_date = $payment->paid_on;
                $transaction->bank_balance = round($newBalance, 2);
                $transaction->transaction_relation = 'payment';
                $transaction->transaction_related_to = $payment->id;
                $transaction->title = 'payment-debited';
                $transaction->save();

                $bankAccount->bank_balance = round($newBalance, 2);
                $bankAccount->save();
            }
        }


    }

    public function updated(Payment $payment)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'payment-updated', $payment->id, 'payment');

        }
    }

    public function deleting(Payment $payment)
    {
        if (!is_null($payment->bank_account_id) && $payment->status == 'complete') {

            $account = $payment->bank_account_id;
            $amount = $payment->amount;

            $bankAccount = BankAccount::find($account);

            if ($bankAccount) {
                $bankBalance = $bankAccount->bank_balance;
                $bankBalance -= $amount;

                $transaction = new BankTransaction();
                $transaction->payment_id = $payment->id;
                $transaction->invoice_id = $payment->invoice_id;
                $transaction->type = 'Dr';
                $transaction->bank_account_id = $account;
                $transaction->amount = round($amount, 2);
                $transaction->transaction_date = $payment->paid_on;
                $transaction->bank_balance = round($bankBalance, 2);
                $transaction->transaction_relation = 'payment';
                $transaction->transaction_related_to = $payment->id;
                $transaction->title = 'payment-deleted';
                $transaction->save();

                $bankAccount->bank_balance = round($bankBalance, 2);
                $bankAccount->save();
            }
        }

        // change invoice status if exists
        if ($payment->invoice) {
            $due = $payment->invoice->amountDue() + $payment->amount;

            if ($due <= 0) {
                $payment->invoice->status = 'paid';
            }
            else if ((float)$due >= (float)$payment->invoice->total) {
                $payment->invoice->status = 'unpaid';
            }
            else {
                $payment->invoice->status = 'partial';
            }

            $payment->invoice->due_amount = $due;

            $payment->invoice->saveQuietly();
        }

        if ($payment->order_id) {
            $order = Order::findOrFail($payment->order_id);
            $order->status = 'pending';
            $order->save();
        }

        if (!is_null($payment->quickbooks_payment_id)) {
            if (quickbooks_setting()->status && quickbooks_setting()->access_token != '') {
                $quickBooks = new QuickbookController();
                $quickBooks->deletePayment($payment);
            }
        }

        $notifyData = ['App\Notifications\NewPayment', 'App\Notifications\PaymentReminder'];
        Notification::deleteNotification($notifyData, $payment->id);

    }

    public function deleted(Payment $payment)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'payment-deleted');

        }
    }

}

