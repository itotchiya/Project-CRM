<?php

namespace App\Observers;

use App\Events\NewIssueEvent;
use App\Models\Issue;
use App\Models\Notification;

class IssueObserver
{

    public function created(Issue $issue)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new NewIssueEvent($issue));
        }
    }

    public function deleting(Issue $issue)
    {
        $notifyData = ['App\Notifications\NewIssue'];
        Notification::deleteNotification($notifyData, $issue->id);

    }

    public function creating(Issue $issue)
    {
        if (company()) {
            $issue->company_id = company()->id;
        }
    }

}
