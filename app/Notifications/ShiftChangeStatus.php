<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\EmployeeShiftChangeRequest;

class ShiftChangeStatus extends BaseNotification
{

    public $employeeShiftSchedule;
    public $emailSetting;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(EmployeeShiftChangeRequest $employeeShiftSchedule)
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
    public function via($notifiable)
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
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

        $content = __('email.shiftChangeStatus.text') . ': ' . __('app.' . $this->employeeShiftSchedule->status) . '<br>'. __('app.date') . ': ' . $this->employeeShiftSchedule->shiftSchedule->date->toFormattedDateString() . '<br>'. __('modules.attendance.shiftName') . ': ' . $this->employeeShiftSchedule->shift->shift_name;

        $build
            ->subject(__('email.shiftChangeStatus.subject') . ' - ' . config('app.name') . '.')
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

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'user_id' => $this->employeeShiftSchedule->shiftSchedule->user_id,
            'status' => $this->employeeShiftSchedule->status,
            'new_shift_id' => $this->employeeShiftSchedule->employee_shift_id,
            'date' => $this->employeeShiftSchedule->shiftSchedule->date
        ];
    }

}
