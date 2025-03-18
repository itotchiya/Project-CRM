<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Ticket
 *
 * @property int $id
 * @property int $user_id
 * @property string $subject
 * @property string $status
 * @property string $priority
 * @property int|null $agent_id
 * @property int|null $channel_id
 * @property int|null $type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $agent
 * @property-read \App\Models\User $client
 * @property-read mixed $created_on
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketReply[] $reply
 * @property-read int|null $reply_count
 * @property-read \App\Models\User $requester
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketTag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketTagList[] $ticketTags
 * @property-read int|null $ticket_tags_count
 * @method static \Database\Factories\TicketFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Query\Builder|Ticket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Ticket withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Ticket withoutTrashed()
 * @property string|null $mobile
 * @property int|null $country_id
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereMobile($value)
 * @property string|null $close_date
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCloseDate($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $extras
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCompanyId($value)
 * @property int|null $ticket_number
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTicketNumber($value)
 * @property int|null $group_id
 * @property-read \App\Models\TicketReply|null $latestReply
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereGroupId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $ticketMention
 * @property-read int|null $ticket_mention_count
 * @property int|null $project_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketActivity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereProjectId($value)
 * @mixin \Eloquent
 */
class Ticket extends BaseModel
{

    use HasCompany, SoftDeletes, HasFactory, CustomFieldsTrait;

    const CUSTOM_FIELD_MODEL = 'App\Models\Ticket';

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['created_on'];

    public function group()
    {
        return $this->belongsTo(TicketGroup::class, 'group_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function reply(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'ticket_id');
    }

    public function latestReply(): HasOne
    {
        return $this->hasOne(TicketReply::class, 'ticket_id')->latest();
    }

    public function tags(): HasMany
    {
        return $this->hasMany(TicketTag::class, 'ticket_id');
    }

    public function ticketTags(): BelongsToMany
    {
        return $this->belongsToMany(TicketTagList::class, 'ticket_tags', 'ticket_id', 'tag_id');
    }

    public function getCreatedOnAttribute()
    {

        if (is_null($this->created_at)) {
            return '';
        }

        return $this->created_at->timezone(company()->timezone)->format('d M Y H:i');
    }

    public function badge($tag = 'p'): string
    {

        $latestReplyUser = $this->latestReply?->user;
        $totalReply = $this->reply()->count();

        $selfReplyCount = $this->reply()->where('user_id', $latestReplyUser?->id)->count();

        if ($totalReply > 1 && ($totalReply !== $selfReplyCount) && $latestReplyUser && $latestReplyUser->id !== user()->id) {
            return '<' . $tag . ' class="mb-0"><span class="badge badge-secondary mr-1 bg-info">' . __('app.newResponse') . '</span></' . $tag . '>';
        }

        return $totalReply == 1 || ($totalReply == $selfReplyCount) ? '<' . $tag . ' class="mb-0"><span class="badge badge-secondary mr-1 bg-dark-green">' . __('app.new') . '</span></' . $tag . '>' : '';
    }

    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    public function ticketMention(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'ticket_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class, 'ticket_id')->latest();
    }

    /*
     * Permissions
     */
    public function hasAllPermission($permission): bool
    {
        return $permission == 'all';
    }

    public function hasAddedPermission($permission): bool
    {
        return $permission == 'added' && user()->id == $this->added_by;
    }

    public function hasOwnedPermission($permission): bool
    {
        return $permission == 'owned' && (user()->id == $this->agent_id || user()->id == $this->user_id);
    }

    public function hasBothPermission($permission): bool
    {
        return $permission == 'both' && (user()->id == $this->agent_id || user()->id == $this->added_by || user()->id == $this->user_id);
    }

    public function canViewTicket(): bool
    {
        return $this->hasPermission(user()->permission('view_tickets'));
    }

    public function canDeleteTicket(): bool
    {
        return $this->hasPermission(user()->permission('delete_tickets'));
    }

    public function canEditTicket(): bool
    {
        return $this->hasPermission(user()->permission('edit_tickets'));
    }

    public function hasPermission($permission): bool
    {
        return $this->hasAllPermission($permission) ||
            $this->hasAddedPermission($permission) ||
            $this->hasOwnedPermission($permission) ||
            $this->hasBothPermission($permission);
    }

    /*
     * Permissions End here
     */
}
