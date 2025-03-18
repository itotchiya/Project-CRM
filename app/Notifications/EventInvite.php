<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\EmailNotificationSetting;

class EventInvite extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
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
     * @param mixed $notifiable
     * @return array
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

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.newEvent.subject'), $this->event->event_name);
        }

        return $via;
    }

    /**
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws \Exception
     */
    public function toMail($notifiable)
    {
        $eventInvite = parent::build($notifiable);
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

        $content = __('email.newEvent.text') . '<br><br><strong>' . __('modules.events.eventName') . ': <strong>' . $this->event->event_name . '<strong><br>' . __('modules.events.startOn') . ': ' . $this->event->start_date_time->translatedFormat($this->company->date_format . ' - ' . $this->company->time_format) . '<br>' . __('modules.events.endOn') . ': ' . $this->event->end_date_time->translatedFormat($this->company->date_format . ' - ' . $this->company->time_format)   . '<br>' . __('app.location') . ': <strong>' . $this->event->where . '<strong>';

        $eventInvite->subject(__('email.newEvent.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.newEvent.action'),
                'notifiableName' => $notifiable->name
            ]);

        $eventInvite->attachData($vFile, 'cal.ics', [
            'mime' => 'text/calendar',
        ]);

        return $eventInvite;
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
        return [
            'id' => $this->event->id,
            'start_date_time' => $this->event->start_date_time->format('Y-m-d H:i:s'),
            'event_name' => $this->event->event_name
        ];
    }

    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)
            ->content(__('email.newEvent.subject') . "\n" . __('modules.events.eventName') . ': ' . $this->event->event_name . "\n" . __('modules.events.startOn') . ': ' . $this->event->start_date_time->format($this->company->date_format . ' - ' . $this->company->time_format) . "\n" . __('modules.events.endOn') . ': ' . $this->event->end_date_time->format($this->company->date_format . ' - ' . $this->company->time_format));

    }

}
