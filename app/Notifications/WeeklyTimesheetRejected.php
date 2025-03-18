<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\WeeklyTimesheet;

class WeeklyTimesheetRejected extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public WeeklyTimesheet $weeklyTimesheet)
    {
        $this->weeklyTimesheet = $weeklyTimesheet;
        $this->company = $this->weeklyTimesheet->company;
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
            ->subject(__('email.weeklyTimesheetRejected.subject'))
            ->greeting(__('email.weeklyTimesheetRejected.greeting'))
            ->line(__('email.weeklyTimesheetRejected.text'))
            ->line(__('email.weeklyTimesheetRejected.text1', ['startDate' => $this->weeklyTimesheet->week_start_date->copy()->translatedFormat($this->company->date_format), 'endDate' => $this->weeklyTimesheet->week_start_date->copy()->addDays(6)->translatedFormat($this->company->date_format)]))
            ->line(__('email.weeklyTimesheetRejected.text2', ['reason' => $this->weeklyTimesheet->reason]))
            ->action(__('email.weeklyTimesheetRejected.action'), route('weekly-timesheets.edit', $this->weeklyTimesheet->id))
            ->line(__('email.weeklyTimesheetRejected.thankYou'));

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
