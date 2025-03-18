<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\TaskUser
 *
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereUserId($value)
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property int|null $task_comment_id
 * @property int|null $task_note_id
 * @property int|null $project_id
 * @property int|null $project_note_id
 * @property int|null $discussion_id
 * @property int|null $discussion_reply_id
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereDiscussionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereDiscussionReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereProjectNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTaskCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTaskNoteId($value)
 * @property int|null $ticket_id
 * @property int|null $event_id
 * @property int|null $user_chat_id
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereUserChatId($value)
 * @mixin \Eloquent
 */
class MentionUser extends Pivot
{

    use Notifiable;

    protected $guarded = ['id'];
    protected $table = 'mention_users';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

}
