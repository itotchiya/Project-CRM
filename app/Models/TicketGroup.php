<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\TicketGroup
 *
 * @property int $id
 * @property string $group_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketAgentGroups[] $agents
 * @property-read int|null $agents_count
 * @property-read mixed $icon
 * @property-read mixed $enabledAgents
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read int|null $enabled_agents_count
 * @method static \Database\Factories\TicketGroupFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereCompanyId($value)
 * @mixin \Eloquent
 */
class TicketGroup extends BaseModel
{

    use HasFactory, HasCompany;

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'group_id');
    }

    public function enabledAgents(): HasMany
    {
        return $this->hasMany(TicketAgentGroups::class, 'group_id')->where('status', '=', 'enabled')->whereHas('user')->groupBy('agent_id');
    }

}
