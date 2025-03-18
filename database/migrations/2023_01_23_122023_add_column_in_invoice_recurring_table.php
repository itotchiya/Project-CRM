<?php

use App\Models\ExpenseRecurring;
use App\Models\Module;
use App\Models\Permission;
use App\Models\RecurringInvoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {

        if (!Schema::hasColumn('invoice_recurring', 'next_invoice_date')) {
            Schema::table('invoice_recurring', function (Blueprint $table) {
                $table->boolean('immediate_invoice')->default(false);
                $table->date('next_invoice_date')->nullable()->after('issue_date');
            });
        }

        if (!Schema::hasColumn('expenses_recurring', 'issue_date')) {
            Schema::table('expenses_recurring', function (Blueprint $table) {
                $table->date('issue_date')->after('billing_cycle');
                $table->boolean('immediate_expense')->default(false)->after('purchase_from');
                $table->date('next_expense_date')->nullable()->after('issue_date');
            });
        }

        $module = Module::where('module_name', 'invoices')->first();

        if ($module) {
            Permission::where('name', 'manage_recurring_invoice')->update(['module_id' => $module->id]);
        }

        $recurringInvoices = RecurringInvoice::with(['recurrings'])->where('status', 'active')->get();

        foreach ($recurringInvoices as $recurring) {

            $totalExistingCount = $recurring->recurrings->count();

            if ($recurring->unlimited_recurring == 1 || ($totalExistingCount < $recurring->billing_cycle)) {
                continue;
            }

            try {
                if (is_null($recurring->issue_date)) {
                    continue;
                }
            } catch (\Exception $exception) {
                echo $exception->getMessage();
            }


            $this->saveNextInvoiceDate($recurring, 'invoice');
        }

        $recurringExpenses = ExpenseRecurring::with(['recurrings'])->where('status', 'active')->get();

        foreach ($recurringExpenses as $recurring) {

            if (!$recurring->recurrings) {
                return false;
            }

            $expense = $recurring->recurrings->first();

            try {
                $recurring->update(['issue_date' => $expense->purchase_date]);
            } catch (\Exception $exception) {
                echo $exception->getMessage();
            }

            $totalExistingCount = $recurring->recurrings->count();

            if ($recurring->unlimited_recurring == 1 || ($totalExistingCount < $recurring->billing_cycle)) {
                continue;
            }

            $this->saveNextInvoiceDate($recurring, 'expense');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_recurring', function (Blueprint $table) {
            $table->dropColumn('immediate_invoice');
            $table->dropColumn('next_invoice_date');
        });

        Schema::table('expenses_recurring', function (Blueprint $table) {
            $table->dropColumn('issue_date');
            $table->dropColumn('immediate_expense');
            $table->dropColumn('next_expense_date');
        });
    }

    private function saveNextInvoiceDate($recurring, $type)
    {
        if (!$recurring->recurrings) {
            return false;
        }

        $issueDate = $recurring->recurrings->last();

        if ($type == 'invoice') {
            $date = $issueDate->issue_date ?? now();
        }
        else {
            $date = $issueDate->purchase_date;
        }

        $days = match ($recurring->rotation) {
            'daily' => $date->addDay(),
            'weekly' => $date->addWeek(),
            'bi-weekly' => $date->addWeeks(2),
            'monthly' => $date->addMonth(),
            'quarterly' => $date->addQuarter(),
            'half-yearly' => $date->addMonths(6),
            'annually' => $date->addYear(),
            default => $date->addDay(),
        };

        if ($type == 'invoice') {
            $recurring->next_invoice_date = $days->format('Y-m-d');
        }
        else {
            $recurring->next_expense_date = $days->format('Y-m-d');
        }


        $recurring->saveQuietly();
    }

};
