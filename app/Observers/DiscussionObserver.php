<?php

namespace App\Observers;

use App\Events\DiscussionEvent;
use App\Events\DiscussionMentionEvent;
use App\Models\Discussion;
use App\Models\Notification;
use App\Models\User;

class DiscussionObserver
{

    public function saving(Discussion $discussion)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $discussion->last_updated_by = user()->id;
        }
    }

    public function creating(Discussion $discussion)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $discussion->last_updated_by = user()->id;
                $discussion->added_by = user()->id;
            }
        }

        if (company()) {
            $discussion->company_id = company()->id;
        }
    }

    public function created(Discussion $discussion)
    {

        $project = $discussion->project;

        $mentionIds = explode(',', request()->mention_user_id);

        $projectUsers = json_decode($project->projectMembers->pluck('id'));

        $mentionUserId = array_intersect($mentionIds, $projectUsers);

        if ($mentionUserId != null && $mentionUserId != '') {

            $discussion->mentionUser()->sync($mentionIds);

            event(new DiscussionMentionEvent($discussion, $mentionUserId));

        }
        else {

            $unmentionIds = array_diff($projectUsers, $mentionIds);

            if ($unmentionIds != null && $unmentionIds != '') {

                $projectMember = User::whereIn('id', $unmentionIds)->get();
                event(new DiscussionEvent($discussion, $projectMember));

            }
            else {
                if (!isRunningInConsoleOrSeeding()) {
                    event(new DiscussionEvent($discussion, null));
                }
            }
        }

    }

    public function deleting(Discussion $discussion)
    {
        $notifyData = ['App\Notifications\NewDiscussion', 'App\Notifications\NewDiscussionReply'];
        Notification::deleteNotification($notifyData, $discussion->id);

    }

}
