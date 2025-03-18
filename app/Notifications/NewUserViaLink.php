<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;

class NewUserViaLink extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $new_user;
    private $emailSetting;

    public function __construct($new_user)
    {
        $this->new_user = $new_user;
        $this->company = $this->new_user->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'user-join-via-invitation')->first();
    }

    /**
     * Get the notification's delivery channels.
     *t('mail::layout')
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

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.newUserViaLink.subject'), $this->new_user->name);
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

        $url = route('employees.show', $this->new_user->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.newUserViaLink.text') . '<br>' . __('app.name') . ':- ' . $this->new_user->name . '<br>' . __('app.email') . ':- ' . $this->new_user->email;

        $build
            ->subject(__('email.newUserViaLink.subject') . ' ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.newUserViaLink.action'),
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
            'user_id' => $this->new_user->id,
            'name' => $this->new_user->name,
            'image_url' => $this->new_user->image_url,
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

        try {
            return $this->slackBuild($notifiable)
                ->content('*' . __('email.newUserViaLink.subject') . ' ' . config('app.name') . '!*' . "\n" . __('email.newUserViaLink.text'). "\n" . __('app.name') . ': ' . $this->new_user->name . "\n" . __('app.email') . ': ' . $this->new_user->email);
        } catch (\Exception $e) {
            return $this->slackRedirectMessage('email.newUserViaLink.subject', $notifiable);
        }

    }

}
