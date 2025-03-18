<?php

namespace App\Notifications;

use App\Models\ProjectTimeLog;
use Illuminate\Notifications\Messages\MailMessage;

class TimerStarted extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */

    private $timeLog;

    public function __construct(ProjectTimeLog $projectTimeLog)
    {
        $this->timeLog = $projectTimeLog;
        $this->company = $this->timeLog->project->company;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    // phpcs:ignore
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $url = route('login');
        $url = getDomainSpecificUrl($url, $this->company);
        $content = __('email.notificationIntro');

        $build
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.notificationAction')
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
        return $this->timeLog->toArray();
    }

}
