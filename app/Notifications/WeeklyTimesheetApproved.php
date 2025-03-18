<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\WeeklyTimesheet;

class WeeklyTimesheetApproved extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public WeeklyTimesheet $weeklyTimesheet)
    {
        $this->weeklyTimesheet = $weeklyTimesheet;
        $this->company = $weeklyTimesheet->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $build = parent::build($notifiable);

        $build  
            ->subject(__('email.weeklyTimesheetApproved.subject'))
            ->greeting(__('email.weeklyTimesheetApproved.greeting'))
            ->line(__('email.weeklyTimesheetApproved.text'))
            ->line(__('email.weeklyTimesheetApproved.text1', ['startDate' => $this->weeklyTimesheet->week_start_date->copy()->translatedFormat($this->company->date_format), 'endDate' => $this->weeklyTimesheet->week_start_date->copy()->addDays(6)->translatedFormat($this->company->date_format), 'employeeName' => $this->weeklyTimesheet->user->name]))
            ->action(__('email.weeklyTimesheetApproved.action'), route('weekly-timesheets.show', $this->weeklyTimesheet->id))
            ->line(__('email.weeklyTimesheetApproved.thankYou'));

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
