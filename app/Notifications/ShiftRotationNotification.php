<?php

namespace App\Notifications;

use App\Models\EmployeeShiftSchedule;
use App\Models\ShiftRotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftRotationNotification extends BaseNotification
{
    use Queueable;

    protected $dates;
    protected $rotationFrequency;
    protected $company;
    protected $userId;

    /**
     * Create a new notification instance.
     */
    public function __construct($dates, $rotationFrequency, $userId)
    {
        $this->dates = $dates;
        $this->rotationFrequency = $rotationFrequency;
        $this->userId = $userId;
        $this->company = $userId->company;
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
    
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $shiftRotations = EmployeeShiftSchedule::with('shift')
        ->whereIn('date', $this->dates)
        ->where('user_id', $this->userId->id)
        ->get();

        $build
            ->subject(__('email.shiftScheduled.subject'))
            ->markdown('mail.shift-rotation-email', [
                'shiftRotations' => $shiftRotations,
                'rotationFrequency' => $this->rotationFrequency,
                'company' => $this->company,
            ]);

        parent::resetLocale();

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
