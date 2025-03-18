<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\TicketReply
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string|null $message
 * @property int|null $added_by
 * @property int|null $agent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $icon
 * @property-read \App\Models\Ticket $ticket
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply newQuery()
 * @method static \Illuminate\Database\Query\Builder|TicketReply onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|TicketReply withTrashed()
 * @method static \Illuminate\Database\Query\Builder|TicketReply withoutTrashed()
 * @property int|null $company_id
 * @property string|null $imap_message_id
 * @property string|null $imap_message_uid
 * @property string|null $imap_in_reply_to
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereImapInReplyTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereImapMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereImapMessageUid($value)
 * @mixin \Eloquent
 */
class TicketReply extends BaseModel
{

    use SoftDeletes;

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(TicketFile::class, 'ticket_reply_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_reply_users', 'ticket_reply_id', 'user_id');
    }

    public function ticketReplyUsers(): HasMany
    {
        return $this->hasMany(TicketReplyUser::class, 'ticket_reply_id');
    }

}
