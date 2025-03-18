<?php

namespace App\Notifications;

use App\Models\Leave;
use App\Models\EmailNotificationSetting;

class LeaveStatusReject extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leave;
    private $emailSetting;

    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
        $this->company = $this->leave->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'new-leave-application')->first();

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
        $url = route('leaves.show', $this->leave->id);

        if ($this->leave->duration == "multiple") {
            $url .= '?type=single';
        }

        $url = getDomainSpecificUrl($url, $this->company);

        if (session()->has('dateRange')) {
            $contentDate = session('dateRange');
        } else {
            $contentDate = $this->leave->leave_date->format($this->company->date_format);
        }

        $content = __('email.leave.reject') . '<br>' . __('app.date') . ': ' . $contentDate . '<br>' . __('app.status') . ': ' . $this->leave->status . '<br>' . __('app.reason') . ': ' . $this->leave->reject_reason . '<br>';

        $build
            ->subject(__('email.leaves.statusSubject') . ' - ' . config('app.name'))
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
        return $this->leave->toArray();
    }

    public function toSlack($notifiable)
    {

            return $this->slackBuild($notifiable)
                ->content(__('email.leave.reject') . "\n" . __('app.date') . ': ' . $this->leave->leave_date->format($this->company->date_format) . "\n" . __('app.status') . ': ' . $this->leave->status . "\n" . __('app.reason') . ': ' . $this->leave->reject_reason);


    }

}
