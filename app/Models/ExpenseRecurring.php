<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ExpenseRecurring
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $currency_id
 * @property int|null $project_id
 * @property int|null $user_id
 * @property int|null $created_by
 * @property string $item_name
 * @property int|null $day_of_month
 * @property int|null $day_of_week
 * @property string|null $payment_method
 * @property string $rotation
 * @property int|null $billing_cycle
 * @property \Illuminate\Support\Carbon $issue_date
 * @property int $unlimited_recurring
 * @property float $price
 * @property string|null $bill
 * @property int $immediate_expense
 * @property string $status
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $next_invoice_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExpensesCategory|null $category
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $bill_url
 * @property-read mixed $created_on
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $total_amount
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Expense[] $recurrings
 * @property-read int|null $recurrings_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereBill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereDayOfMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereRotation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereUnlimitedRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereUserId($value)
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereLastUpdatedBy($value)
 * @property string|null $purchase_from
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring wherePurchaseFrom($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCompanyId($value)
 * @property \Illuminate\Support\Carbon|null $next_expense_date
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereImmediateExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereNextExpenseDate($value)
 * @property int|null $bank_account_id
 * @property-read \App\Models\BankAccount|null $bank
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereBankAccountId($value)
 * @mixin \Eloquent
 */
class ExpenseRecurring extends BaseModel
{

    use CustomFieldsTrait, HasCompany;

    protected $casts = [
        'issue_date' => 'datetime',
        'created_at' => 'datetime',
        'next_expense_date' => 'datetime',
    ];
    protected $with = ['currency', 'company:id'];

    protected $appends = ['total_amount', 'created_on', 'bill_url'];

    protected $table = 'expenses_recurring';

    const ROTATION_COLOR = [
        'daily' => 'success',
        'weekly' => 'info',
        'monthly' => 'secondary',
        'bi-weekly' => 'warning',
        'quarterly' => 'light',
        'half-yearly' => 'dark',
        'annually' => 'success',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpensesCategory::class, 'category_id');
    }

    public function recurrings(): HasMany
    {
        return $this->hasMany(Expense::class, 'expenses_recurring_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function getTotalAmountAttribute()
    {
        if (!is_null($this->price) && !is_null($this->currency_id)) {
            return currency_format($this->price, $this->currency->id);
        }

        return '';
    }

    public function getCreatedOnAttribute()
    {
        if (!is_null($this->created_at)) {
            return $this->created_at->format($this->company->date_format);
        }

        return '';
    }

    public function getBillUrlAttribute()
    {
        return ($this->bill) ? asset_url_local_s3(Expense::FILE_PATH . '/' . $this->bill) : '';
    }

}
