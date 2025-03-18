<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Scopes\ActiveScope;

/**
 * App\Models\TaskCommentEmoji
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $comment_id
 * @property string|null $emoji_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TaskComment|null $taskComment
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereEmojiName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereUserId($value)
 * @mixin \Eloquent
 */
class TaskCommentEmoji extends BaseModel
{

    public function taskComment(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

}
