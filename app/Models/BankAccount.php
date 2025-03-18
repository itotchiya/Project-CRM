<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\BankAccount
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $type
 * @property string|null $bank_name
 * @property string|null $account_name
 * @property string|null $account_number
 * @property string|null $account_type
 * @property int|null $currency_id
 * @property string|null $contact_number
 * @property float|null $opening_balance
 * @property string|null $bank_logo
 * @property int|null $status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property float|null $bank_balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $file_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BankTransaction[] $transaction
 * @property-read int|null $transaction_count
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereBankBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereBankLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BankAccount extends BaseModel
{
    const FILE_PATH = 'bank-logo';

    use HasCompany;

    protected $with = ['currency'];
    protected $appends = ['file_url'];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transaction(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }

    public function getFileUrlAttribute()
    {
        if ($this->bank_logo != '') {
            return asset_url_local_s3(BankAccount::FILE_PATH.'/' . $this->bank_logo);

        } elseif ($this->type == 'bank'){
            return '<i class="bi bi-bank"></i>';

        } elseif ($this->type == 'cash'){
            return '<i class="bi bi-cash-coin"></i>';
        }
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

}
