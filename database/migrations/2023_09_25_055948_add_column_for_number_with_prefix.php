<?php

use App\Helper\NumberFormat;
use App\Models\Company;
use App\Models\Contract;
use App\Models\CreditNotes;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        if (!Schema::hasColumn('credit_notes', 'original_credit_note_number')) {
            Schema::table('credit_notes', function (Blueprint $table) {
                $table->string('original_credit_note_number')->nullable()->after('cn_number');
            });
        }

        if (!Schema::hasColumn('contracts', 'original_contract_number')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->string('contract_number')->nullable()->change();
                $table->string('original_contract_number')->nullable()->after('contract_number');
            });
        }

        if (!Schema::hasColumn('estimates', 'original_estimate_number')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->string('estimate_number')->nullable()->change();
                $table->string('original_estimate_number')->nullable()->after('estimate_number');
            });
        }

        if (!Schema::hasColumn('orders', 'original_order_number')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('order_number')->nullable()->change();
                $table->string('original_order_number')->nullable()->after('order_number');
            });
        }

        if (!Schema::hasColumn('invoices', 'original_invoice_number')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('invoice_number')->change();
                $table->string('original_invoice_number')->nullable()->after('invoice_number');
            });
        }


        $companeis = Company::get();

        foreach ($companeis as $company) {
            $invoiceSettings = InvoiceSetting::withoutGlobalScopes()->where('company_id', $company->id)->first();

            $creditNotes = CreditNotes::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($creditNotes as $creditNote) {
                $creditNote->cn_number = NumberFormat::creditNote($creditNote->cn_number, $invoiceSettings);
                $creditNote->original_credit_note_number = str($creditNote->cn_number)->replace($invoiceSettings->credit_note_prefix . $invoiceSettings->credit_note_number_separator, '');
                $creditNote->saveQuietly();
            }

            $contracts = Contract::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($contracts as $contract) {
                $contract->contract_number = NumberFormat::contract($contract->contract_number, $invoiceSettings);
                $contract->original_contract_number = str($contract->contract_number)->replace($invoiceSettings->contract_prefix . $invoiceSettings->contract_number_separator, '');
                $contract->saveQuietly();
            }

            $invoices = Invoice::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($invoices as $invoice) {
                $invoice->invoice_number = NumberFormat::invoice($invoice->invoice_number, $invoiceSettings);
                $invoice->original_invoice_number = str($invoice->invoice_number)->replace($invoiceSettings->invoice_prefix . $invoiceSettings->invoice_number_separator, '');
                $invoice->saveQuietly();
            }

            $estimates = Estimate::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($estimates as $estimate) {
                $estimate->estimate_number = NumberFormat::estimate($estimate->estimate_number, $invoiceSettings);
                $estimate->original_estimate_number = str($estimate->estimate_number)->replace($invoiceSettings->estimate_prefix . $invoiceSettings->estimate_number_separator, '');
                $estimate->saveQuietly();
            }

            $orders = Order::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($orders as $order) {
                $order->order_number = NumberFormat::order($order->order_number, $invoiceSettings);
                $order->original_order_number = str($order->order_number)->replace($invoiceSettings->order_prefix . $invoiceSettings->order_number_separator, '');
                $order->saveQuietly();
            }
        }

    }

};
