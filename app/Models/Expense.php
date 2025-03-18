<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Expense
 *
 * @property int $id
 * @property string $item_name
 * @property double $total
 * @property string $date
 * @property double $total
 * @property string $exchange_rate
 * @property \Illuminate\Support\Carbon $purchase_date
 * @property string|null $purchase_from
 * @property float $price
 * @property float $default_currency_price
 * @property int $currency_id
 * @property int|null $project_id
 * @property string|null $bill
 * @property int $user_id
 * @property int|null $approver_id
 * @property int|null $default_currency_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $can_claim
 * @property int|null $category_id
 * @property int|null $expenses_recurring_id
 * @property int|null $created_by
 * @property string|null $description
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Currency $currency
 * @property-read mixed $bill_url
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $purchase_on
 * @property-read mixed $total_amount
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|Expense[] $recurrings
 * @property-read int|null $recurrings_count
 * @property-read \App\Models\User $user
 * @property-read \App\Models\User $approver
 * @method static \Database\Factories\ExpenseFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Expense query()
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereBill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCanClaim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereExpensesRecurringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense wherePurchaseFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereUserId($value)
 * @property-read \App\Models\ExpensesCategory|null $category
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCompanyId($value)
 * @property int|null $bank_account_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BankTransaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereDefaultCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereExchangeRate($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BankTransaction> $transactions
 * @mixin \Eloquent
 */
class Expense extends BaseModel
{

    use CustomFieldsTrait, HasFactory, HasCompany;

    const FILE_PATH = 'expense-invoice';
    const CUSTOM_FIELD_MODEL = 'App\Models\Expense';

    protected $casts = [
        'purchase_date' => 'datetime',
        'purchase_on' => 'datetime',
    ];
    protected $appends = ['total_amount', 'purchase_on', 'bill_url', 'default_currency_price'];
    protected $with = ['currency', 'company:id'];

    public function getBillUrlAttribute()
    {
        return ($this->bill) ? asset_url_local_s3(Expense::FILE_PATH . '/' . $this->bill) : '';
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpensesCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function recurrings(): HasMany
    {
        return $this->hasMany(Expense::class, 'parent_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'expense_id');
    }

    public function getTotalAmountAttribute()
    {

        if (!is_null($this->price) && !is_null($this->currency_id)) {
            return currency_format($this->price, $this->currency_id);
        }

        return '';
    }

    public function getPurchaseOnAttribute()
    {
        if (is_null($this->purchase_date)) {
            return '';
        }

        return $this->purchase_date->format($this->company ? $this->company->date_format : company()->date_format);

    }

    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    public function defaultCurrencyPrice() : Attribute
    {
        return Attribute::make(
            get: function () {
                $currency = (company() == null) ? $this->company->currency_id : company()->currency_id;
                if ($this->currency_id == $currency) {
                    return $this->price;
                }

                if(!$this->exchange_rate){
                    return $this->price;
                }

                return ($this->price * ((float)$this->exchange_rate));
            },
        );
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

}
