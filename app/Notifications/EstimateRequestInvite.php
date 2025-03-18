<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class EstimateRequestInvite extends BaseNotification
{

    /**
     * @var User
     */
    private $invite;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $invite)
    {
        $this->invite = $invite;
        $this->company = $invite->company;
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
        return ['mail'];
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
        $build = parent::build();
        $url = route('estimate-request.create');
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.estimate_request_invite.content');
        $subject = __('email.estimate_request_invite.subject');

        $build
            ->subject($subject)
            ->markdown('mail.email', [
            'url' => $url,
            'content' => $content,
            'themeColor' => $this->company->header_color,
            'actionText' => __('email.estimate_request_invite.action'),
            'notifiableName' => $this->invite->name,

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
