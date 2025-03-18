<?php

namespace App\Notifications;

use App\Models\User;

class NewCustomer extends BaseNotification
{


    private $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->company = $this->user->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {

        $via = ['mail', 'database'];

        if ($this->company->slackSetting->status == 'active') {
            array_push($via, 'slack');
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
        $url = route('clients.show', $this->user->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.newCustomer.text') . '<br>' . __('app.name') . ': ' . $this->user->name . '<br>' . __('app.email') . ': ' . $this->user->email;

        $build
            ->subject(__('email.newCustomer.subject') . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('app.view') . ' ' . __('app.client'),
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
            'id' => $this->user->id,
            'name' => $this->user->name
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
            $url = route('clients.show', $this->user->id);
            $url = getDomainSpecificUrl($url, $this->company);
            $content = '*' . __('email.newCustomer.subject') . ' ' . config('app.name') . '!*' . "\n" . __('email.newCustomer.text') . "\n" . __('app.name') . ': ' . $this->user->name . "\n" . __('app.email') . ': ' . $this->user->email;
            return $this->slackBuild($notifiable)->content($content );
        } catch (\Exception $e) {
            return $this->slackRedirectMessage('email.newCustomer.subject', $notifiable);
        }
    }

}
