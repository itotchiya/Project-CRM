<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Event
 *
 * @property int $id
 * @property string $event_name
 * @property string $label_color
 * @property string $where
 * @property string $description
 * @property \Illuminate\Support\Carbon $start_date_time
 * @property \Illuminate\Support\Carbon $end_date_time
 * @property string $repeat
 * @property int|null $repeat_every
 * @property int|null $repeat_cycles
 * @property string $repeat_type
 * @property string $send_reminder
 * @property int|null $remind_time
 * @property string $remind_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventAttendee[] $attendee
 * @property-read int|null $attendee_count
 * @property-read mixed $icon
 * @method static \Database\Factories\EventFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndDateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRemindTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRemindType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRepeat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRepeatCycles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRepeatEvery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRepeatType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereSendReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartDateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereWhere($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventId($value)
 * @property int|null $company_id
 * @property string|null $event_link
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventFile[] $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventLink($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionEvent
 * @property-read int|null $mention_event_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property int|null $parent_id
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereParentId($value)
 * @mixin \Eloquent
 */
class Event extends BaseModel
{

    use HasFactory, HasCompany;

    protected $casts = [
        'start_date_time' => 'datetime',
        'end_date_time' => 'datetime',
    ];
    protected $fillable = ['start_date_time', 'end_date_time', 'event_name', 'where', 'description'];

    public function attendee(): HasMany
    {
        return $this->hasMany(EventAttendee::class, 'event_id');
    }

    public function getUsers()
    {
        $userArray = [];

        foreach ($this->attendee as $attendee) {
            array_push($userArray, $attendee->user()->select('id', 'email', 'name', 'email_notifications')->first());
        }

        return collect($userArray);
    }

    public function files()
    {
        return $this->hasMany(EventFile::class, 'event_id')->orderByDesc('id');
    }

    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    public function mentionEvent(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'host');
    }

}
