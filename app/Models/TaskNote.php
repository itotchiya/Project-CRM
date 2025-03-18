<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\TaskNote
 *
 * @property int $id
 * @property int $task_id
 * @property int|null $user_id
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionNote
 * @property-read int|null $mention_note_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionNote
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 */
class TaskNote extends BaseModel
{

    protected $with = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    public function mentionNote(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'task_note_id');
    }

}
