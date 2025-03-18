<?php

namespace App\Notifications;

use App\Models\GlobalSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;

class AttendanceReminder extends BaseNotification
{

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($notifiable->email != '') {
            $via = ['mail'];
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $this->company = $notifiable->company;

        $url = route('dashboard');
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.AttendanceReminder.text');

        $build
            ->subject(__('email.AttendanceReminder.subject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.AttendanceReminder.action'), 'notifiableName' => $notifiable->name
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
    public function toArray($notifiable): array
    {
        return $notifiable->toArray();
    }

}
