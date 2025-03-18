<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\UserChat;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewChat extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $userChat;
    private $emailSetting;

    public function __construct(UserChat $userChat)
    {
        $this->userChat = $userChat;
        $this->emailSetting = EmailNotificationSetting::where('slug', 'message-notification')->first();
        $this->company = $this->userChat->company;

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
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_slack == 'yes' && slack_setting()->status == 'active') {
            array_push($via, 'slack');
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.newChat.subject'), strip_tags($this->userChat->message));
        }

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
        $content = $this->userChat->message;
        $url = route('messages.index');
        $url = getDomainSpecificUrl($url, $this->company);

        $build
            ->subject(__('email.newChat.subject') . ' ' . __('app.from') . ' ' . $this->userChat->fromUser->name)
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.newChat.action'),
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
        return [
            'id' => $this->userChat->id,
            'user_one' => $this->userChat->user_one,
            'from_name' => $this->userChat->fromUser->name,
        ];
    }

    public function toSlack($notifiable)
    {
        $url = route('messages.index');
        $url = getDomainSpecificUrl($url, $this->company);

        return $this->slackBuild($notifiable)
            ->content('<' . $url . '|' . __('email.newChat.subject') . ' ' . __('app.from') . ' ' . $this->userChat->fromUser->name . '>');


    }

    public function toOneSignal()
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.newChat.subject') . ' ' . __('app.from') . ' ' . $this->userChat->fromUser->name)
            ->setBody($this->userChat->message)
            ->setUrl(route('messages.index'));
    }

}
