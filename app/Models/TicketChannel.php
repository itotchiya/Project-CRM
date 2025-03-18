<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\TicketChannel
 *
 * @property int $id
 * @property string $channel_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereChannelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereCompanyId($value)
 * @mixin \Eloquent
 */
class TicketChannel extends BaseModel
{

    use HasCompany;

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'channel_id');
    }

}
