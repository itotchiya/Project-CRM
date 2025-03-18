<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ExpensesCategory
 *
 * @property int $id
 * @property string $category_name
 * @property bigint $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Expense[] $expense
 * @property-read int|null $expense_count
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|ExpensesCategoryRole[] $roles
 * @property-read int|null $roles_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Expense|null $expenses
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereCompanyId($value)
 * @property-read int|null $expenses_count
 * @mixin \Eloquent
 */
class ExpensesCategory extends BaseModel
{

    use HasCompany;

    protected $table = 'expenses_category';
    protected $default = ['id', 'category_name'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(ExpensesCategoryRole::class, 'expenses_category_id');
    }

}
