<?php

namespace App\Listeners;

use App\Events\ProjectNoteEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewProjectNote;

class ProjectNoteListener
{

    /**
     * Handle the event.
     *
     * @param ProjectNoteEvent $event
     * @return void
     */

    public function handle(ProjectNoteEvent $event)
    {
        Notification::send($event->unmentionUser, new NewProjectNote($event->project, $event));

    }

}
