<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class RemovalRequestApprovedReject extends BaseNotification
{


    protected $type;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = array();

        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);

        if ($this->type == 'approved') {

            $content = __('email.removalRequestApproved.text');

            $build
                ->subject(__('email.removalRequestApproved.subject') . ' ' . config('app.name') . '.')
                ->markdown('mail.email', [
                    'content' => $content,
                    'notifiableName' => $notifiable->client_name
                ]);

            parent::resetLocale();

            return $build;
        }

        $content = __('email.removalRequestReject.text');

        $build
            ->subject(__('email.removalRequestReject.subject') . ' ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'content' => $content,
                'notifiableName' => $notifiable->client_name
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
