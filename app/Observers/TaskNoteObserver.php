<?php

namespace App\Observers;

use App\Events\TaskNoteEvent;
use App\Events\TaskNoteMentionEvent;
use App\Models\MentionUser;
use App\Models\Task;
use App\Models\TaskNote;
use App\Models\User;

class TaskNoteObserver
{

    public function saving(TaskNote $note)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $note->last_updated_by = user()->id;
        }
    }

    public function creating(TaskNote $note)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $note->added_by = user()->id;
        }
    }

    public function created(TaskNote $note)
    {
        if (isRunningInConsoleOrSeeding()) {
            return true;
        }

        $task = $note->task;

        if ($task->project_id != null) {

            if (request()->mention_user_id != null && request()->mention_user_id != '') {

                $note->mentionUser()->sync(request()->mention_user_id);

                $taskUsers = json_decode($task->taskUsers->pluck('user_id'));

                $mentionIds = json_decode($note->mentionNote->pluck('user_id'));

                $mentionUserId = array_intersect($mentionIds, $taskUsers);

                if ($mentionUserId != null && $mentionUserId != '') {

                    event(new TaskNoteMentionEvent($task, $note->created_at, $mentionUserId));
                }

                $unmentionIds = array_diff($taskUsers, $mentionIds);

                if ($unmentionIds != null && $unmentionIds != '') {

                    $taskUsersNote = User::whereIn('id', $unmentionIds)->get();

                    if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {

                        event(new TaskNoteEvent($task, $note->created_at, $task->project->client, 'client'));

                    }

                    event(new TaskNoteEvent($task, $note->created_at, $taskUsersNote));

                }

            }
            else {

                event(new TaskNoteEvent($task, $note->created_at, $task->project->projectMembers));
            }

            if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {

                event(new TaskNoteEvent($task, $note->created_at, $task->project->client, 'client'));

            }

        }
        else {
            event(new TaskNoteEvent($task, $note->created_at, $task->users));
        }
    }

    public function updating(TaskNote $note)
    {

        $mentionedUser = MentionUser::where('task_note_id', $note->id)->pluck('user_id');
        $requestMentionIds = request()->mention_user_id;
        $newMention = [];
        $note->mentionUser()->sync(request()->mention_user_id);

        if ($requestMentionIds != null) {

            foreach ($requestMentionIds as $value) {

                if (($mentionedUser) != null) {

                    if (!in_array($value, json_decode($mentionedUser))) {

                        $newMention[] = $value;
                    }
                }
                else {

                    $newMention[] = $value;
                }
            }

            if (!empty($newMention)) {

                event(new TaskNoteMentionEvent($note->task, $note, $newMention));

            }

        }

    }

}
