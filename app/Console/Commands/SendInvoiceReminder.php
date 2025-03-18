<?php

namespace App\Console\Commands;

use App\Events\InvoiceReminderAfterEvent;
use App\Events\InvoiceReminderEvent;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use Illuminate\Console\Command;

class SendInvoiceReminder extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-invoice-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send invoice reminder to the client before and after due date of invoice';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        Company::active()->select('id', 'timezone')->with('currency')->chunk(50, function ($companies) {

            foreach ($companies as $company) {
                $invoice_setting = InvoiceSetting::where('company_id', $company->id)->first();

                $invoices = Invoice::whereNotNull('due_date')
                    ->where('status', '!=', 'paid')
                    ->where('status', '!=', 'canceled')
                    ->where('status', '!=', 'draft')
                    ->where('company_id', $company->id);


                if ($invoice_setting->send_reminder != 0) {
                    $invoicesBefore = $invoices
                        ->whereDate('due_date', now($company->timezone)->addDays($invoice_setting->send_reminder))
                        ->get();

                    foreach ($invoicesBefore as $invoice) {
                        $notifyUser = $invoice->client;

                        if (!is_null($notifyUser)) {
                            event(new InvoiceReminderEvent($invoice, $notifyUser, $invoice_setting->send_reminder));
                        }
                    }
                }

                if ($invoice_setting->reminder == 'after') {
                    $invoicesAfter = $invoices
                        ->whereDate('due_date', now($company->timezone)->subDays($invoice_setting->send_reminder_after))
                        ->get();

                    foreach ($invoicesAfter as $invoice) {
                        $notifyUser = $invoice->client;

                        if (!is_null($notifyUser)) {
                            event(new InvoiceReminderAfterEvent($invoice, $notifyUser, $invoice_setting->send_reminder_after));
                        }

                    }

                }
                else {
                    $invoicesEvery = $invoices
                        ->whereDate('due_date', '<', now($company->timezone))
                        ->get();

                    foreach ($invoicesEvery as $invoice) {
                        $notifyUser = $invoice->client;
                        $date_diff = $invoice->due_date->diffInDays(now());

                        if ($invoice_setting->send_reminder_after != 0) {
                            if ($date_diff % $invoice_setting->send_reminder_after == 0 && !is_null($notifyUser)) {
                                event(new InvoiceReminderAfterEvent($invoice, $notifyUser, $invoice_setting->send_reminder_after));
                            }
                        }

                    }

                }
            }
        });

        return Command::SUCCESS;
    }

}
