<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\UnitType
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $unit_type
 * @property int $default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read int|null $creditnoteitems_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read int|null $estimateitems_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read int|null $estimatetemplate_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read int|null $invoices_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read int|null $proposalitems_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read int|null $proposaltemplate_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read int|null $recurring_invoice_count
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType query()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereUnitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @mixin \Eloquent
 */
class UnitType extends BaseModel
{
    use HasCompany;

    protected $table = 'unit_types';
    protected $id = 'id';
    protected $fillable = ['unit_type', 'company_id', 'default'];

    public function invoicesItems()
    {
        return $this->hasMany(InvoiceItems::class);
    }

    public function proposalitems()
    {
        return $this->hasMany(ProposalItem::class);
    }

    public function estimateitems()
    {
        return $this->hasMany(EstimateItem::class);
    }

    public function creditnoteitems()
    {
        return $this->hasMany(CreditNotes::class);
    }

    public function proposaltemplate()
    {
        return $this->hasMany(ProposalTemplate::class);
    }

    public function estimatetemplate()
    {
        return $this->hasMany(EstimateTemplate::class);
    }

    public function recurringInvoice()
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}