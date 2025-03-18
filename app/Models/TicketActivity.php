<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TicketActivity
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property int|null $assigned_to
 * @property int|null $channel_id
 * @property int|null $group_id
 * @property int|null $type_id
 * @property string $status
 * @property string $priority
 * @property string $type
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $assignedTo
 * @property-read \App\Models\TicketChannel|null $channel
 * @property-read \App\Models\TicketGroup|null $group
 * @property-read \App\Models\Ticket $ticket
 * @property-read \App\Models\TicketType|null $ticketType
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereUserId($value)
 * @mixin \Eloquent
 */
class TicketActivity extends BaseModel
{

    protected $with = ['assignedTo', 'channel', 'group', 'ticketType'];
    protected $appends = ['details'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(TicketChannel::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TicketGroup::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'type_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function details(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->type) {
                    'create' => __('modules.tickets.activity.create'),
                    'reply' => __('modules.tickets.activity.reply', ['userName' => $this->user->name]),
                    'note' => __('modules.tickets.activity.note', ['userName' => $this->user->name]),
                    'group' => __('modules.tickets.activity.group', ['groupName' => $this->group?->group_name ?: '--']),
                    'assign' => __('modules.tickets.activity.assign', ['userName' => $this->assignedTo?->name ?: '--']),
                    'priority' => __('modules.tickets.activity.priority', ['priority' => __('app.'.$this->priority)]),
                    'type' => __('modules.tickets.activity.type', ['type' => $this->ticketType?->type ?: '--']),
                    'channel' => __('modules.tickets.activity.channel', ['channel' => $this->channel?->channel_name ?: '--']),
                    'status' => __('modules.tickets.activity.status', ['status' => __('app.'.$this->status)]),
                    default => '',
                };
            }
        );
    }

}
