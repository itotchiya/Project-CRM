<?php

namespace App\Listeners;

use App\Events\ShiftRotationEvent;
use App\Models\ShiftRotation;
use App\Notifications\ShiftRotationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;


class ShiftRotationListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ShiftRotationEvent $event): void
    {
        $users = $event->employeeData;
        Notification::send($users, new ShiftRotationNotification($event->dates, $event->rotationFrequency, $users));
    }
}
