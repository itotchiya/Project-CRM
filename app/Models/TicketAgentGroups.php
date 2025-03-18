<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TicketAgentGroups
 *
 * @property int $id
 * @property int $agent_id
 * @property int|null $group_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\TicketGroup|null $group
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereUpdatedAt($value)
 * @property int|null $company_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereLastUpdatedBy($value)
 * @mixin \Eloquent
 */
class TicketAgentGroups extends BaseModel
{

    use HasCompany;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TicketGroup::class, 'group_id');
    }

}
