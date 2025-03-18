<?php

namespace App\Listeners;

use App\Models\User;
use App\Scopes\ActiveScope;
use App\Events\NewProjectEvent;
use App\Notifications\NewProject;
use App\Notifications\NewProjectMember;
use App\Notifications\NewProjectStatus;
use App\Notifications\ProjectMemberMention;
use App\Notifications\ProjectRating;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class NewProjectListener
{

    /**
     * @param NewProjectEvent $event
     */

    public function handle(NewProjectEvent $event)
    {
        if ($event->project->client_id != null) {
            $clientId = $event->project->client_id;
            // Notify client
            $notifyUsers = User::withoutGlobalScope(ActiveScope::class)->findOrFail($clientId);

            if (!is_null($notifyUsers) && $event->projectStatus == 'NewProjectClient') {

                Notification::send($notifyUsers, new NewProject($event->project));
            }
        }

        $projectMembers = $event->project->projectMembers;

        if ($event->projectStatus == 'statusChange') {
            if (!is_null($event->notifyUser) && !($event->notifyUser instanceof Collection)) {
                $event->notifyUser->notify(new NewProjectStatus($event->project));
            }

            Notification::send($projectMembers, new NewProjectStatus($event->project));
        }

        if ($event->notificationName == 'NewProject') {

            Notification::send($event->notifyUser, new NewProjectMember($event->project));

        }
        elseif ($event->notificationName == 'ProjectMention') {

            Notification::send($event->notifyUser, new ProjectMemberMention($event->project));

        }
        elseif ($event->notificationName == 'ProjectRating') {

            Notification::send($event->notifyUser, new ProjectRating($event->project));

        }

    }

}
