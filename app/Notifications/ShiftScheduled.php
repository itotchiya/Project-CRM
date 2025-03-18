<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\EmployeeShiftSchedule;

class ShiftScheduled extends BaseNotification
{


    public $employeeShiftSchedule;
    public $emailSetting;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        $this->employeeShiftSchedule = $employeeShiftSchedule;
        $this->company = $this->employeeShiftSchedule->shift->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'shift-assign-notification')->first();
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

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.shiftScheduled.subject') . ' : ' . $this->employeeShiftSchedule->date->toFormattedDateString(), $this->employeeShiftSchedule->shift->shift_name);
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
        $url = route('dashboard');
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('app.date') . ': ' . $this->employeeShiftSchedule->date->toFormattedDateString() . '<br>' . __('modules.attendance.shiftName') . ': ' . $this->employeeShiftSchedule->shift->shift_name . '<br>';

        $build
            ->subject(__('email.shiftScheduled.subject') . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.loginDashboard'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    public function toArray()
    {
        return [
            'user_id' => $this->employeeShiftSchedule->user_id,
            'shift_id' => $this->employeeShiftSchedule->employee_shift_id,
            'date' => $this->employeeShiftSchedule->date
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
            ->content('*' . __('email.shiftScheduled.subject') . '!*' . "\n" . __('app.date') . ': ' . $this->employeeShiftSchedule->date->toFormattedDateString() . "\n" . __('modules.attendance.shiftName') . ': ' . $this->employeeShiftSchedule->shift->shift_name);

    }

}
