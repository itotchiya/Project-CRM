<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Appreciation;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewAppreciation extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $userAppreciation;
    private $emailSetting;

    public function __construct(Appreciation $userAppreciation)
    {

        $this->userAppreciation = $userAppreciation;
        $this->emailSetting = EmailNotificationSetting::where('slug', 'appreciation-notification')->first();
        $this->company = $this->userAppreciation->company;

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
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.newAppreciation.subject'), $this->userAppreciation->award->title);
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
        $content = __('email.newAppreciation.text', ['award' => $this->userAppreciation->award->title, 'award_at' => $this->userAppreciation->award_date->format($this->company->date_format)]);
        $url = route('appreciations.show', $this->userAppreciation->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $build
            ->subject(__('email.newAppreciation.subject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.newAppreciation.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->userAppreciation->id,
            'created_at' => $this->userAppreciation->created_at->format('Y-m-d H:i:s'),
            'award_at' => $this->userAppreciation->award_date->format('Y-m-d H:i:s'),
            'heading' => $this->userAppreciation->award->title,
            'icon' => $this->userAppreciation->award->awardIcon->icon,
            'color_code' => $this->userAppreciation->award->color_code,
            'image_url' => $this->userAppreciation->addedBy->image_url
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {

        $url = route('appreciations.show', $this->userAppreciation->id);
        $url = getDomainSpecificUrl($url, $this->company);

        return $this->slackBuild($notifiable)
            ->content('*' . __('email.newAppreciation.subject') . '*' . "\n" . '<' . $url . '|' . $this->userAppreciation->award->title . '>');


    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.newAppreciation.subject'))
            ->setBody($this->userAppreciation->award->title);
    }

}
