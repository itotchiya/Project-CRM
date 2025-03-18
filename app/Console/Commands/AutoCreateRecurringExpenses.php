<?php

namespace App\Console\Commands;

use App\Events\NewExpenseEvent;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseRecurring;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCreateRecurringExpenses extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring-expenses-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto create recurring expenses ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {

        Company::active()
            ->select([
                'companies.id as id',
                'timezone',
                'expenses_recurring.id as rid',
                'expenses_recurring.*',
                DB::raw('count(expenses.id) as expense_count')
            ])
            ->rightJoin('expenses_recurring', 'expenses_recurring.company_id', '=', 'companies.id')
            ->leftJoin('expenses', function ($join) {
                $join->on('expenses.expenses_recurring_id', '=', 'expenses_recurring.id')
                    ->where('expenses_recurring.status', 'active');
            })
            ->groupBy('companies.id', 'timezone', 'expenses_recurring.id')
            ->whereNotNull('next_expense_date')
            ->chunk(50, function ($companies) {
                foreach ($companies as $company) {
                    $this->createRecurringExpenses($company);
                }
            });


        return Command::SUCCESS;
    }

    private function createRecurringExpenses($company): void
    {
        $totalExistingCount = $company->expense_count;

        if ($company->unlimited_recurring == 1 || ($totalExistingCount < $company->billing_cycle)) {

            if ((Carbon::parse($company->issue_date, $company->timezone)->isToday() && $totalExistingCount == 0) || (Carbon::parse($company->next_expense_date, $company->timezone)->isToday())) {
                $this->info('Running for recurring expense:' . $company->id);
                $this->makeExpense($company);
                $this->saveNextInvoiceDate($company);
            }
        }

    }

    private function makeExpense($recurring)
    {
        $expense = new Expense();
        $expense->company_id = $recurring->company_id;
        $expense->expenses_recurring_id = $recurring->rid;
        $expense->category_id = $recurring->category_id;
        $expense->project_id = $recurring->project_id;
        $expense->currency_id = $recurring->currency_id;
        $expense->user_id = $recurring->user_id;
        $expense->created_by = $recurring->created_by;
        $expense->item_name = $recurring->item_name;
        $expense->description = $recurring->description;
        $expense->price = $recurring->price;
        $expense->purchase_from = $recurring->purchase_from;
        $expense->added_by = $recurring->added_by;
        $expense->bank_account_id = $recurring->bank_account_id;
        $expense->purchase_date = now($recurring->timezone)->format('Y-m-d');
        $expense->status = 'approved';
        $expense->save();

        if ($expense->user_id != '') {
            event(new NewExpenseEvent($expense, 'member'));
            event(new NewExpenseEvent($expense, 'admin'));
        }
    }

    private function saveNextInvoiceDate($recurring)
    {
        $days = match ($recurring->rotation) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'bi-weekly' => now()->addWeeks(2),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addQuarter(),
            'half-yearly' => now()->addMonths(6),
            'annually' => now()->addYear(),
            default => now()->addDay(),
        };
        $totalExistingCount = $recurring->expense_count + 1;

        $days = ($totalExistingCount === $recurring->billing_cycle) ? null : $days->setTimezone($recurring->timezone)->format('Y-m-d');
        ExpenseRecurring::where('id', $recurring->rid)->update(['next_expense_date' => $days]);
    }

}
