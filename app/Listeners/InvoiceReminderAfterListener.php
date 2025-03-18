<?php

namespace App\Listeners;

use App\Events\InvoiceReminderAfterEvent;
use App\Notifications\InvoiceReminderAfter;
use Notification;

class InvoiceReminderAfterListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */

    public function handle(InvoiceReminderAfterEvent $event)
    {
        Notification::send($event->notifyUser, new InvoiceReminderAfter($event->invoice));
    }

}
