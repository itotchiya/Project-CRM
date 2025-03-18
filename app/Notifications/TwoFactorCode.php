<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class TwoFactorCode extends BaseNotification
{


    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    //phpcs:ignore
    public function via($notifiable)
    {
        return ['mail'];
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

        $twoFaCode = '<p style="color:#1d82f5"><strong>' . $notifiable->two_factor_code . '</strong></p>';

        $content = __('email.twoFactor.line1') . '<br>' . new HtmlString($twoFaCode) . '<br>' . __('email.twoFactor.line2') . '<br>' . __('email.twoFactor.line3');

        $build
            ->markdown('mail.email', [
                'content' => $content,
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

}
