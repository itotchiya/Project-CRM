<?php

namespace App\Jobs;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpensesCategory;
use App\Models\ExpensesCategoryRole;
use App\Models\Role;
use App\Models\User;
use App\Traits\ExcelImportable;
use App\Traits\UniversalSearchTrait;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportExpenseJob implements ShouldQueue, ShouldBeUnique
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait;
    use ExcelImportable;

    private $row;
    private $columns;
    private $company;
    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
        $this->user = user();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->isColumnExists('item_name') && $this->isColumnExists('price') && $this->isColumnExists('purchase_date')) {

            DB::beginTransaction();
            try {

                $expense = new Expense();
                $expense->company_id = $this->company->id;
                $expense->item_name = $this->getColumnValue('item_name');
                $expense->purchase_date = $this->isColumnExists('purchase_date') ? Carbon::createFromFormat('Y-m-d', $this->getColumnValue('purchase_date')) : Carbon::now();
                $expense->purchase_from = $this->getColumnValue('purchase_from');
                $expense->price = round((float)$this->getColumnValue('price'), 2);
                $expense->currency_id = $this->company->currency_id;
                $expense->default_currency_id = $this->company->currency_id;
                $expense->exchange_rate = 1;
                $expense->description = $this->getColumnValue('description');

                $userId = null;

                if ($this->isEmailValid($this->getColumnValue('email'))) {

                    $user = User::where('email', $this->getColumnValue('email'))->where('company_id', $this->company->id)->onlyEmployee()->first();

                    if ($user) {
                        $userId = $user->id;
                    }
                }

                $expense->user_id = $userId;

                if ($this->getColumnValue('category')) {
                    $category = ExpensesCategory::where('category_name', $this->getColumnValue('category'))->where('company_id', $this->company->id)->first();

                    if (!$category) {
                        $category = new ExpensesCategory();
                        $category->category_name = $this->getColumnValue('category');
                        $category->company_id = $this->company->id;
                        $category->save();

                        $rolesData = Role::where('name', '<>', 'admin')->where('name', '<>', 'client')->where('company_id', $this->company->id)->get();

                        foreach ($rolesData as $roleData) {
                            $expansesCategoryRoles = new ExpensesCategoryRole();
                            $expansesCategoryRoles->expenses_category_id = $category->id;
                            $expansesCategoryRoles->role_id = $roleData->id;
                            $expansesCategoryRoles->company_id = $this->company->id;
                            $expansesCategoryRoles->save();
                        }
                    }

                    $expense->category_id = $category->id;
                }

                $bankAccount = BankAccount::where('account_name', $this->getColumnValue('bank_account'))->where('company_id', $this->company->id)->first();
                $expense->bank_account_id = $bankAccount?->id;

                $expense->save();

                DB::commit();
            } catch (InvalidFormatException $e) {
                DB::rollBack();
                $this->failJob(__('messages.invalidDate'));
            } catch (Exception $e) {
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }
        }
        else {
            $this->failJob(__('messages.invalidData'));
        }
    }

}
