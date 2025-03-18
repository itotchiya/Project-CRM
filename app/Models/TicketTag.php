<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TicketTag
 *
 * @property int $id
 * @property int $tag_id
 * @property int $ticket_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\TicketTagList $tag
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereCompanyId($value)
 * @mixin \Eloquent
 */
class TicketTag extends BaseModel
{

    use HasCompany;

    protected $guarded = ['id'];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(TicketTagList::class, 'tag_id');
    }

}
