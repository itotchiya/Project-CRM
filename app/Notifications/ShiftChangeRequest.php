<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\EmployeeShiftChangeRequest;

class ShiftChangeRequest extends BaseNotification
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
    // phpcs:ignore
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
        $url = route('shifts-change.index');
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.shiftChange.text') . '<br>' . __('app.employee') . ': ' . $this->employeeShiftSchedule->shiftSchedule->user->name . '<br>'. __('app.date') . ': ' . $this->employeeShiftSchedule->shiftSchedule->date->toFormattedDateString() . '<br>'. __('app.previous') . ' ' . __('modules.attendance.shiftName') . ': ' . $this->employeeShiftSchedule->shiftSchedule->shift->shift_name . '<br>' . __('app.new') . ' ' . __('modules.attendance.shiftName') . ': ' . $this->employeeShiftSchedule->shift->shift_name;

        $build
            ->subject(__('email.shiftChange.subject') . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.shiftChange.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    public function toArray()
    {
        return [
            'user_id' => $this->employeeShiftSchedule->shiftSchedule->user_id,
            'shift_id' => $this->employeeShiftSchedule->shiftSchedule->employee_shift_id,
            'new_shift_id' => $this->employeeShiftSchedule->employee_shift_id,
            'date' => $this->employeeShiftSchedule->shiftSchedule->date
        ];
    }

}
