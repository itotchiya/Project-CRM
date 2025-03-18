<?php

namespace App\Observers;

use App\Events\ProjectNoteEvent;
use App\Events\ProjectNoteMentionEvent;
use App\Models\MentionUser;
use App\Models\ProjectNote;
use App\Models\User;

// use function GuzzleHttp\json_decode;

class ProjectNoteObserver
{

    /**
     * @param ProjectNote $ProjectNote
     */
    public function saving(ProjectNote $ProjectNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $ProjectNote->last_updated_by = user()->id;
        }
    }

    public function creating(ProjectNote $ProjectNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $ProjectNote->added_by = user()->id;
        }
    }

    public function created(ProjectNote $projectNote)
    {
        $project = $projectNote->project;

        if (request()->mention_user_id != null && request()->mention_user_id != '') {

            $projectNote->mentionUser()->sync(request()->mention_user_id);

            $projectUsers = json_decode($project->projectMembers->pluck('id'));

            $mentionIds = json_decode($projectNote->mentionNote->pluck('user_id'));

            $mentionUserId = array_intersect($mentionIds, $projectUsers);

            if ($mentionUserId != null && $mentionUserId != '') {

                event(new ProjectNoteMentionEvent($project, $projectNote->created_at, $mentionUserId));

            }

            $unmentionIds = array_diff($projectUsers, $mentionIds);

            if ($unmentionIds != null && $unmentionIds != '') {

                $projectNoteUsers = User::whereIn('id', $unmentionIds)->get();
                event(new ProjectNoteEvent($project, $projectNote->created_at, $projectNoteUsers));

            }

        }
        else {

            event(new ProjectNoteEvent($project, $projectNote->created_at, $projectNote->project->projectMembers));

        }

    }

    public function updating(ProjectNote $projectNote)
    {

        $mentionedUser = MentionUser::where('project_note_id', $projectNote->id)->pluck('user_id');

        $requestMentionIds = explode(',', (request()->mention_user_id));
        $project = $projectNote->project;
        $newMention = [];

        if (request()->mention_user_id != null) {
            $projectNote->mentionUser()->sync($requestMentionIds);

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

            if ($newMention != null) {

                event(new ProjectNoteMentionEvent($project, $projectNote->created_at, $newMention));

            }

        }
    }

}
