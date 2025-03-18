<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\User;

class NewUserSlack extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $emailSetting;

    public function __construct(User $user)
    {
        $this->company = $user->company;

        // When there is company of user.
        if ($this->company) {
            $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'user-registrationadded-by-admin')->first();
        }

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

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        return $via;
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
        return $notifiable->toArray();
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

            $url = route('login');
            $url = getDomainSpecificUrl($url, $this->company);

            $content = '*' . __('email.newUser.subject') . ' ' . config('app.name') . '!*' . "\n" . __('email.newUser.text');
            $url = "\n" . '<' . $url . '|' . __('email.newUser.action') . '>';

            return $this->slackBuild($notifiable)->content($content . $url);

        } catch (\Exception $e) {
            return $this->slackRedirectMessage('email.newUser.subject', $notifiable);
        }

    }

}
