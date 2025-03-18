<?php

namespace App\Observers;

use App\Events\DiscussionEvent;
use App\Events\DiscussionMentionEvent;
use App\Models\DiscussionReply;
use App\Events\DiscussionReplyEvent;
use App\Models\User;

class DiscussionReplyObserver
{

    public function creating(DiscussionReply $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    public function created(DiscussionReply $discussionReply)
    {
        if (isset(request()->discussion_type) && request()->discussion_type == 'discussion_reply') {
            $discussion = $discussionReply->discussion;

            $project = $discussion->project;

            $mentionIds = explode(',', request()->mention_user_id);

            $projectUsers = json_decode($project->projectMembers->pluck('id'));
            $mentionUserId = array_intersect($mentionIds, $projectUsers);

            if ($mentionUserId != null && $mentionUserId != '') {

                $discussionReply->mentionUser()->sync($mentionIds);
                event(new DiscussionMentionEvent($discussion, $mentionUserId));

            }
            else {

                $unmentionIds = array_diff($projectUsers, $mentionIds);

                if ($unmentionIds != null && $unmentionIds != '') {

                    $project_member = User::whereIn('id', $unmentionIds)->get();
                    event(new DiscussionEvent($discussion, $project_member));

                }
                else {
                    if (!isRunningInConsoleOrSeeding()) {
                        $discussion->last_reply_at = now()->timezone('UTC')->toDateTimeString();
                        $discussion->last_reply_by_id = user()->id;
                        $discussion->save();

                        event(new DiscussionReplyEvent($discussionReply, $discussion->user));
                    }
                }
            }
        }

    }

    public function deleted(DiscussionReply $discussionReply)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $discussion = $discussionReply->discussion;
            $discussion->best_answer_id = null;
            $discussion->save();
        }
    }

}
