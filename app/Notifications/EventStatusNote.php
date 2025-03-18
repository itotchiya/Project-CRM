<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Event;

class EventStatusNote extends BaseNotification
{

    /**
     * Create a new notification instance.
     */
    private $event;
    private $emailSetting;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->company = $this->event->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'event-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');

        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $eventStatusNote = parent::build($notifiable);
        $vCalendar = new \Eluceo\iCal\Component\Calendar('www.example.com');
        $vEvent = new \Eluceo\iCal\Component\Event();
        $vEvent
            ->setDtStart(new \DateTime($this->event->start_date_time))
            ->setDtEnd(new \DateTime($this->event->end_date_time))
            ->setNoTime(true)
            ->setSummary($this->event->event_name);
        $vCalendar->addComponent($vEvent);
        $vFile = $vCalendar->render();

        $url = route('events.show', $this->event->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.newEvent.eventCancelNote') . '<br><br>' . __('modules.events.eventName') . ': <strong>' . $this->event->event_name . '<strong><br>' . __('modules.events.startOn') . ': ' . $this->event->start_date_time->translatedFormat($this->company->date_format . ' - ' . $this->company->time_format) . '<br>' . __('modules.events.endOn') . ': ' . $this->event->end_date_time->translatedFormat($this->company->date_format . ' - ' . $this->company->time_format);

        $eventStatusNote->subject(__('email.newEvent.statusSubject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.newEvent.action'),
                'notifiableName' => $notifiable->name
            ]);

        return $eventStatusNote;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->event->id,
            'start_date_time' => $this->event->start_date_time->format('Y-m-d H:i:s'),
            'event_name' => $this->event->event_name
        ];
    }

    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)
            ->content("*" . __('email.newEvent.statusSubject') . "*" . "\n" . __('email.newEvent.eventCancelNote') . "\n" . __('modules.events.eventName') . ': ' . $this->event->event_name . "\n" . __('modules.events.startOn') . ': ' . $this->event->start_date_time->format($this->company->date_format . ' - ' . $this->company->time_format) . "\n" . __('modules.events.endOn') . ': ' . $this->event->end_date_time->format($this->company->date_format . ' - ' . $this->company->time_format));

    }

}
