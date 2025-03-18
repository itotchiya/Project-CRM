<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\GlobalSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\HtmlString;

class BirthdayReminder extends BaseNotification
{

    private $birthDays;
    private $count;
    private $emailSetting;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->birthDays = $event;
        $this->count = count($this->birthDays->upcomingBirthdays);
        $this->company = $this->birthDays->company;

        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'birthday-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {

        $via = array('database');

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active' && $notifiable->employeeDetail->slack_username != '') {
            array_push($via, 'slack');
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

        $list = '<ol>';

        foreach ($this->birthDays->upcomingBirthdays as $birthDay) {
            $list .= '<li>' . $birthDay['name'] . '</li>';
        }

        $list .= '</ol>';

        $url = route('dashboard');
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.BirthdayReminder.text') . '<br>' . new HtmlString($list);

        $build
            ->subject($this->count . ' ' . __('email.BirthdayReminder.subject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.BirthdayReminder.action')
            ]);

        parent::resetLocale();

        return $build;
    }

    public function toArray()
    {
        return ['birthday_name' => $this->birthDays->upcomingBirthdays];
    }

    public function toSlack($notifiable) // phpcs:ignore
    {
        $name = '';

        foreach ($this->birthDays->upcomingBirthdays as $key => $birthDay) {
            $name .= '>' . ($key + 1) . '. ' . $birthDay['name'] . "\n";
        }

        if ($notifiable->employeeDetail->slack_username) {
            return $this->slackBuild($notifiable)
                ->content('>*' . __('email.BirthdayReminder.text') . ' :birthday: *' . "\n" . $name . ' ');
        }


        return $this->slackRedirectMessage('email.BirthdayReminder.text', $notifiable);

    }

}
