<?php

namespace App\Models;

use App\Models\User;
use App\Traits\HasCompany;
use App\Scopes\ActiveScope;
use App\Models\ClientDetails;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateRequest extends BaseModel
{
    use HasCompany;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public static function lastRequestNumber()
    {
        return (int)EstimateRequest::orderBy('id', 'desc')->first()?->original_request_number ?? 0;
    }

}
