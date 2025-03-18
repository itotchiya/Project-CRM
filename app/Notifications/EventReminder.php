<?php

namespace App\Notifications;

use App\Models\Event;

class EventReminder extends BaseNotification
{

    private $event;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->company = $this->event->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = array('database');

        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $build = parent::build($notifiable);
        $url = route('events.show', $this->event->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.eventReminder.text') . '<br>' . __('app.name') . ': ' . $this->event->event_name . '<br>' . __('app.venue') . ': ' . $this->event->where . '<br>' . __('app.time') . ': ' . $this->event->start_date_time->toDayDateTimeString();

        $build
            ->subject(__('email.eventReminder.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.eventReminder.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    //phpcs:ignore
    public function toArray($notifiable)
    {
        return $this->event->toArray();
    }

}
