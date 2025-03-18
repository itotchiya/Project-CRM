<?php

namespace App\Observers;

use App\Models\CreditNotes;
use App\Events\NewCreditNoteEvent;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Traits\UnitTypeSaveTrait;
use App\Traits\EmployeeActivityTrait;

class CreditNoteObserver
{

    use UnitTypeSaveTrait;
    use EmployeeActivityTrait;

    public function saving(CreditNotes $creditNote)
    {
        if (\user()) {
            $creditNote->last_updated_by = user()->id;
        }
    }

    public function creating(CreditNotes $creditNote)
    {
        if (\user()) {
            $creditNote->added_by = user()->id;
        }

        if (company()) {
            $creditNote->company_id = company()->id;
        }

        if (is_numeric($creditNote->cn_number)) {
            $creditNote->cn_number = $creditNote->formatCreditNoteNumber();
        }

        $invoiceSettings = company() ? company()->invoiceSetting : $creditNote->company->invoiceSetting;
        $creditNote->original_credit_note_number = str($creditNote->cn_number)->replace($invoiceSettings->credit_note_prefix . $invoiceSettings->credit_note_number_separator, '');

    }

    public function deleting(CreditNotes $creditNote)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $creditNote->id)->where('module_type', 'creditNote')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifyData = ['App\Notifications\NewCreditNote'];
        Notification::deleteNotification($notifyData, $creditNote->id);

    }

    public function created(CreditNotes $creditNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'creditNote-created', $creditNote->id, 'credit_note');
            }


            $clientId = null;

            if ($creditNote->client_id) {
                $clientId = $creditNote->client_id;
            }
            elseif ($creditNote->invoice && $creditNote->invoice->client_id != null) {
                $clientId = $creditNote->invoice->client_id;
            }
            elseif ($creditNote->project && $creditNote->project->client_id != null) {
                $clientId = $creditNote->project->client_id;
            }
            elseif ($creditNote->invoice->project && $creditNote->invoice->project->client_id != null) {
                $clientId = $creditNote->invoice->project->client_id;
            }

            if ($clientId) {
                $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($clientId);
                // Notify client
                if ($notifyUser) {
                    event(new NewCreditNoteEvent($creditNote, $notifyUser));
                }
            }

            if (isset($creditNote->invoice) && $creditNote->invoice->status == 'partial') {
                /* Make and entry in payment table */
                $payment = new Payment();
                $payment->invoice_id = $creditNote->invoice->id;
                $payment->customer_id = $creditNote->invoice->client_id;
                $payment->credit_notes_id = $creditNote->id;
                $payment->amount = $creditNote->invoice->amountDue();
                $payment->gateway = 'Credit Note';
                $payment->currency_id = $creditNote->invoice->currency_id;
                $payment->status = 'complete';
                $payment->paid_on = now();
                $payment->save();
            }

        }
    }

    public function updated(CreditNotes $creditNote)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'creditNote-updated', $creditNote->id, 'credit_note');
        }
    }

    public function deleted(CreditNotes $creditNote)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'creditNote-deleted');

        }
    }

}
