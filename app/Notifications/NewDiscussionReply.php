<?php

namespace App\Notifications;

use App\Models\DiscussionReply;
use App\Models\EmailNotificationSetting;
use Illuminate\Support\HtmlString;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewDiscussionReply extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $discussionReply;
    private $emailSetting;

    public function __construct(DiscussionReply $discussionReply)
    {
        $this->discussionReply = $discussionReply;
        $this->company = $this->discussionReply->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'discussion-reply')->first();
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

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds,$this->discussionReply->user->name . ' ' . __('email.discussionReply.subject'), $this->discussionReply->discussion->title);
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
        $url = route('discussion.show', $this->discussionReply->discussion_id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.discussionReply.text') . ' ' . $this->discussionReply->discussion->title . ':-' . '<br>' . new HtmlString($this->discussionReply->body);

        $build
            ->subject($this->discussionReply->user->name . ' ' . __('email.discussionReply.subject') . $this->discussionReply->discussion->title . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.discussionReply.action'),
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
            'id' => $this->discussionReply->id,
            'title' => $this->discussionReply->discussion->title,
            'discussion_id' => $this->discussionReply->discussion_id,
            'user' => $this->discussionReply->user->name,
            'body' => $this->discussionReply->body,
            'project_id' => $this->discussionReply->discussion->project_id
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

        return $this->slackBuild($notifiable)
            ->content('*' . $this->discussionReply->user->name . ' ' . __('email.discussionReply.subject') . $this->discussionReply->discussion->title . '*' . "\n" . $this->discussionReply->body);


    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.discussionReply.subject'))
            ->setBody($this->discussionReply->discussion->title);
    }

}
