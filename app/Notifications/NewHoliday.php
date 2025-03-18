<?php

namespace App\Notifications;

use App\Models\Holiday;
use App\Models\EmailNotificationSetting;

class NewHoliday extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $holiday;
    private $emailSetting;

    public function __construct(Holiday $holiday)
    {
        $this->holiday = $holiday;
        $this->company = $this->holiday->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
                                ->where('slug', 'holiday-notification')
                                ->first();
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

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.holidays.subject'), $this->holiday->occassion);
        }

        return $via;
    }

    /**
     * @param mixed $notifiable
     * @return MailMessage
     * @throws \Exception
     */
    public function toMail($notifiable)
    {
        $build = parent::build($notifiable);

        $url = route('holidays.show', $this->holiday->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.holidays.text') . '<br><br>' . __('app.occassion') . ': <strong>' . $this->holiday->occassion . '</strong><br>' . __('app.date') . ': <strong>' . $this->holiday->date->translatedFormat($this->company->date_format) . '</strong>';

        $build->subject(__('email.holidays.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.leaves.action'),
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
            'id' => $this->holiday->id,
            'holiday_date' => $this->holiday->date->format('Y-m-d H:i:s'),
            'holiday_name' => $this->holiday->occassion
        ];
    }

    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)
            ->content(__('email.holidays.subject') . "\n" . $notifiable->name . "\n" . '*' . __('app.date') . '*: ' . $this->holiday->date->format($this->company->date_format) . "\n" . __('modules.holiday.occasion') . ': ' . $this->holiday->occassion);

    }

}
