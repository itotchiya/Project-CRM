<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeActivity extends BaseModel
{
    use HasFactory;
    protected $table = 'employee_activity';

    protected $fillable = [
        'emp_id',
        'employee_activity',
        'leave_id',
        'task_id',
        'proj_id',
        'invoice_id',
        'ticket_id',
        'proposal_id',
        'estimate_id',
        'deal_id',
        'deal_followup_id',
        'client_id',
        'expenses_id',
        'timelog_id',
        'event_id',
        'product_id',
        'credit_note_id',
        'payment_id',
        'order_id',
        'contract_id',
    ];
    protected $with = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emp_id')->withoutGlobalScope(ActiveScope::class);
    }

}
