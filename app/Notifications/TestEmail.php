<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class TestEmail extends BaseNotification
{


    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    // phpcs:ignore
    public function via($notifiable)
    {
        $via = array();
        array_push($via, 'mail');

        return $via;
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
        $url = getDomainSpecificUrl(route('login'));
        $content = __('email.test.text');

        $build
            ->subject(__('email.test.subject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
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
        return [
            //
        ];
    }

}
