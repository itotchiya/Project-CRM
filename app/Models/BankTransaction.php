<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\BankTransaction
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $bank_account_id
 * @property int|null $payment_id
 * @property int|null $invoice_id
 * @property int|null $expense_id
 * @property float|null $amount
 * @property string $type
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property string|null $memo
 * @property string|null $transaction_relation
 * @property string|null $transaction_related_to
 * @property string|null $title
 * @property \Illuminate\Support\Carbon|null $transaction_date
 * @property float|null $bank_balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read BankAccount|null $bankAccount
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereBankBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereMemo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereTransactionRelatedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereTransactionRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereUpdatedAt($value)
 * @property int|null $purchase_payment_id
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction wherePurchasePaymentId($value)
 * @mixin \Eloquent
 */
class BankTransaction extends BaseModel
{
    use HasCompany;
    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

}
