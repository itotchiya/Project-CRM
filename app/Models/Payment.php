<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Payment
 *
 * @property int $id
 * @property string $date
 * @property string $exchange_rate
 * @property string $total
 * @property string $project_name
 * @property int|null $project_id
 * @property int|null $invoice_id
 * @property float $amount
 * @property string|null $gateway
 * @property string|null $transaction_id
 * @property string $exchange_rate
 * @property float $price
 * @property float $default_currency_price
 * @property int|null $currency_id
 * @property string|null $plan_id
 * @property string|null $customer_id
 * @property string|null $client_id
 * @property string|null $event_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $paid_on
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $offline_method_id
 * @property string|null $bill
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property int|null $default_currency_id
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read mixed $paid_date
 * @property-read mixed $total_amount
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\OfflinePaymentMethod|null $offlineMethod
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereBill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereOfflineMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePaidOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @property int|null $order_id
 * @property string|null $payment_gateway_response null = success
 * @method static \Illuminate\Database\Eloquent\Builder|Payment completed()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePaymentGatewayResponse($value)
 * @property int|null $credit_notes_id
 * @property string|null $payload_id
 * @property-read \App\Models\CreditNotes|null $creditNote
 * @property-read \App\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreditNotesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePayloadId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCompanyId($value)
 * @property int|null $bank_account_id
 * @property int|null $quickbooks_payment_id
 * @property-read OfflinePaymentMethod|null $offlineMethods
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BankTransaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDefaultCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereQuickbooksPaymentId($value)
 * @mixin \Eloquent
 */
class Payment extends BaseModel
{

    use HasCompany;

    const FILE_PATH = 'payment-receipt';

    protected $casts = [
        'paid_on' => 'datetime',
        'payment_gateway_response' => 'object'
    ];

    protected $appends = ['total_amount', 'paid_date', 'file_url', 'default_currency_price'];
    protected $with = ['currency', 'order'];

    public function client()
    {
        if (!is_null($this->project_id) && $this->project->client_id) {
            return $this->project->client;
        }

        if ($this->invoice_id != null) {
            if ($this->invoice->client_id) {
                return $this->invoice->client;
            }

            if (!is_null($this->invoice->project_id) && $this->invoice->project->client_id) {
                return $this->invoice->project->client;
            }
        }

        return null;
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNotes::class, 'credit_notes_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'payment_id');
    }

    public function offlineMethod(): BelongsTo
    {
        return $this->belongsTo(OfflinePaymentMethod::class, 'offline_method_id');
    }

    public function getTotalAmountAttribute()
    {
        return (!is_null($this->amount) && !is_null($this->currency_id)) ? $this->amount : '';
    }

    public function getPaidDateAttribute()
    {
        return !is_null($this->paid_on) ? Carbon::parse($this->paid_on)->format('d F, Y H:i A') : '';
    }

    public function getFileUrlAttribute()
    {
        return asset_url_local_s3(Payment::FILE_PATH . '/' . $this->bill);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'complete');
    }

    public function offlineMethods(): BelongsTo
    {
        return $this->belongsTo(OfflinePaymentMethod::class, 'offline_method_id');
    }

    public function defaultCurrencyPrice() : Attribute
    {
        return Attribute::make(
            get: function () {
                $currency = (company() == null) ? $this->company->currency_id : company()->currency_id;
                if ($this->currency_id == $currency) {
                    return $this->amount;
                }

                if($this->exchange_rate){
                    return ($this->amount * ((float)$this->exchange_rate));
                }

                // Retrieve the currency associated with the payment
                $currency = Currency::find($this->currency_id);

                if($currency && $currency->exchange_rate){
                    return ($this->amount * ((float)$currency->exchange_rate));
                }

                // If exchange rate is not available or invalid, return the original amount
                return $this->amount;
            },
        );
    }

}
