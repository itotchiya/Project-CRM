<?php

namespace App\Listeners;

use App\Events\NewIssueEvent;
use App\Notifications\NewIssue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class NewIssueListener
{

    /**
     * Handle the event.
     *
     * @param NewIssueEvent $event
     * @return void
     */

    public function handle(NewIssueEvent $event)
    {
        Notification::send(User::allAdmins($event->issue->company->id), new NewIssue($event->issue));
    }

}
